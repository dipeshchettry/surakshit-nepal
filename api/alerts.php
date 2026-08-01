<?php
// ============================================================
// Surakshit Nepal — Disaster Alerts API
// GET /api/alerts.php?lat=27.7&lon=85.3
// Aggregates: USGS Earthquakes + GDACS + ReliefWeb
// ============================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

$lat = sanitize_float($_GET['lat'] ?? 27.7);
$lon = sanitize_float($_GET['lon'] ?? 85.3);

$alerts = [];

// ----------------------------------------------------------
// 1. USGS Earthquake API (last 7 days, M2.5+, within ~500km)
// ----------------------------------------------------------
$minmag  = 2.5;
$radius  = 5; // degrees ~550km
$latMin  = max(NEPAL_LAT_MIN, $lat - $radius);
$latMax  = min(NEPAL_LAT_MAX, $lat + $radius);
$lonMin  = max(NEPAL_LON_MIN, $lon - $radius);
$lonMax  = min(NEPAL_LON_MAX, $lon + $radius);

$usgsUrl = USGS_EARTHQUAKE_API
    . '?format=geojson'
    . '&starttime=' . date('Y-m-d', strtotime('-7 days'))
    . '&endtime='   . date('Y-m-d')
    . '&minmagnitude=' . $minmag
    . '&minlatitude='  . $latMin
    . '&maxlatitude='  . $latMax
    . '&minlongitude=' . $lonMin
    . '&maxlongitude=' . $lonMax
    . '&orderby=time'
    . '&limit=20';

$usgsRes = http_get($usgsUrl, 15);
if ($usgsRes['success']) {
    $usgs = json_decode($usgsRes['body'], true);
    foreach (($usgs['features'] ?? []) as $feat) {
        $p   = $feat['properties'] ?? [];
        $geo = $feat['geometry']['coordinates'] ?? [0, 0, 0];
        $mag = $p['mag'] ?? 0;

        $severity = match(true) {
            $mag >= 6.5 => 'red',
            $mag >= 5.0 => 'orange',
            $mag >= 3.5 => 'yellow',
            default     => 'green',
        };

        $tips = 'Drop, Cover, and Hold On. Move away from windows. '
              . 'Do not use elevators. Check for gas leaks after shaking stops.';

        $alerts[] = [
            'id'           => $feat['id'],
            'type'         => 'earthquake',
            'title'        => 'Earthquake M' . $mag . ' — ' . ($p['place'] ?? 'Near Nepal'),
            'severity'     => $severity,
            'magnitude'    => $mag,
            'depth_km'     => round(($geo[2] ?? 0), 1),
            'lat'          => $geo[1] ?? $lat,
            'lon'          => $geo[0] ?? $lon,
            'affected_area'=> $p['place'] ?? 'Nepal Region',
            'time'         => date('Y-m-d H:i:s', intval(($p['time'] ?? 0) / 1000)),
            'source'       => 'USGS',
            'source_url'   => $p['url'] ?? '',
            'safety_tips'  => $tips,
        ];
    }
}

// ----------------------------------------------------------
// 2. ReliefWeb API — Nepal disasters (last 30 days)
// ----------------------------------------------------------
$rwUrl  = RELIEFWEB_API
    . '?appname=surakshit_nepal'
    . '&profile=list'
    . '&filter[operator]=AND'
    . '&filter[conditions][0][field]=country.iso3&filter[conditions][0][value]=NPL'
    . '&filter[conditions][1][field]=status&filter[conditions][1][value]=ongoing'
    . '&fields[include][]=name&fields[include][]=date&fields[include][]=type'
    . '&fields[include][]=status&fields[include][]=description'
    . '&limit=10';

$rwRes = http_get($rwUrl, 15);
if ($rwRes['success']) {
    $rw = json_decode($rwRes['body'], true);
    foreach (($rw['data'] ?? []) as $item) {
        $fields = $item['fields'] ?? [];
        $typeArr = $fields['type'] ?? [['name' => 'Other']];
        $typeName = strtolower($typeArr[0]['name'] ?? 'other');

        $typeMap = [
            'flood'      => 'flood',
            'landslide'  => 'landslide',
            'earthquake' => 'earthquake',
            'storm'      => 'storm',
            'cold wave'  => 'cold_wave',
            'heat wave'  => 'heatwave',
            'snow'       => 'snowfall',
        ];
        $alertType = 'other';
        foreach ($typeMap as $k => $v) {
            if (str_contains($typeName, $k)) { $alertType = $v; break; }
        }

        $alerts[] = [
            'id'           => 'rw-' . $item['id'],
            'type'         => $alertType,
            'title'        => $fields['name'] ?? 'Disaster Alert — Nepal',
            'severity'     => 'orange',
            'lat'          => 27.7,
            'lon'          => 85.3,
            'affected_area'=> 'Nepal',
            'time'         => $fields['date']['created'] ?? date('Y-m-d H:i:s'),
            'source'       => 'ReliefWeb',
            'source_url'   => 'https://reliefweb.int/disaster/' . $item['id'],
            'safety_tips'  => 'Follow official instructions. Evacuate if advised. Keep emergency kit ready.',
        ];
    }
}

// ----------------------------------------------------------
// 3. GDACS RSS Feed (global disaster alerts near Nepal)
// ----------------------------------------------------------
$gdacsRes = http_get(GDACS_API, 15);
if ($gdacsRes['success']) {
    // Parse RSS XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($gdacsRes['body']);
    if ($xml) {
        foreach (($xml->channel->item ?? []) as $item) {
            $geo = $item->children('geo', true);
            $itemLat = (float)($geo->lat ?? 27.7);
            $itemLon = (float)($geo->long ?? 85.3);

            // Only include if near Nepal
            if (abs($itemLat - 27.7) > 10 || abs($itemLon - 84.0) > 15) continue;

            $gdacs = $item->children('gdacs', true);
            $sev   = strtolower((string)($gdacs->alertlevel ?? 'green'));
            $evType = strtolower((string)($gdacs->eventtype ?? 'other'));
            $typeMap2 = ['EQ'=>'earthquake','FL'=>'flood','TC'=>'storm','DR'=>'other','WF'=>'other','VO'=>'other'];
            $alertType = $typeMap2[$evType] ?? 'other';

            $alerts[] = [
                'id'           => 'gdacs-' . md5((string)$item->guid),
                'type'         => $alertType,
                'title'        => (string)$item->title,
                'severity'     => in_array($sev, ['green','yellow','orange','red']) ? $sev : 'yellow',
                'lat'          => $itemLat,
                'lon'          => $itemLon,
                'affected_area'=> (string)($gdacs->country ?? 'Nepal Region'),
                'time'         => date('Y-m-d H:i:s', strtotime((string)$item->pubDate)),
                'source'       => 'GDACS',
                'source_url'   => (string)($item->link ?? ''),
                'safety_tips'  => 'Monitor official channels. Follow evacuation orders if issued.',
            ];
        }
    }
}

// --- Cache alerts in DB ---
try {
    $db = Database::getInstance();
    foreach ($alerts as $a) {
        $stmt = $db->prepare(
            'INSERT INTO alerts (alert_id,type,title,severity,affected_area,lat,lon,source,source_url,expected_time,safety_tips)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE severity=VALUES(severity), updated_at=NOW()'
        );
        $stmt->execute([
            $a['id'], $a['type'], $a['title'], $a['severity'],
            $a['affected_area'] ?? null, $a['lat'] ?? null, $a['lon'] ?? null,
            $a['source'], $a['source_url'] ?? null,
            $a['time'] ?? null, $a['safety_tips'] ?? null,
        ]);
    }
} catch (Exception $e) {
    // Non-fatal
}

json_response(['alerts' => $alerts, 'count' => count($alerts), 'generated_at' => date('c')]);
