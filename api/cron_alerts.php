<?php
// ============================================================
// Surakshit Nepal — CRON Job: Real-time Alert Push Notifications
// Run this script every 5 minutes via cron/Task Scheduler.
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

// Avoid browser timeouts if run manually
set_time_limit(120);

// Rate limiter for "Poor Man's Cron" (prevents spamming APIs if triggered by frontend)
$lockFile = __DIR__ . '/cron_last_run.txt';
if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 300) {
    exit; // Exit silently if run within the last 5 minutes
}
touch($lockFile);

echo "Starting Real-time Alert Cron Job...\n";

// Use central Nepal coordinates for background fetching
$lat = 27.7;
$lon = 85.3;

$alerts = [];

// 1. USGS Earthquakes
$radius = 5;
$latMin = max(NEPAL_LAT_MIN, $lat - $radius);
$latMax = min(NEPAL_LAT_MAX, $lat + $radius);
$lonMin = max(NEPAL_LON_MIN, $lon - $radius);
$lonMax = min(NEPAL_LON_MAX, $lon + $radius);

$usgsUrl = USGS_EARTHQUAKE_API
    . '?format=geojson'
    . '&starttime=' . date('Y-m-d', strtotime('-2 days'))
    . '&endtime='   . date('Y-m-d', strtotime('+1 days'))
    . '&minmagnitude=4.0' // Only alert for M4.0+
    . '&minlatitude='  . $latMin
    . '&maxlatitude='  . $latMax
    . '&minlongitude=' . $lonMin
    . '&maxlongitude=' . $lonMax
    . '&orderby=time'
    . '&limit=10';

$usgsRes = http_get($usgsUrl, 15);
if ($usgsRes['success']) {
    $usgs = json_decode($usgsRes['body'], true);
    foreach (($usgs['features'] ?? []) as $feat) {
        $p = $feat['properties'] ?? [];
        $geo = $feat['geometry']['coordinates'] ?? [0, 0, 0];
        $mag = $p['mag'] ?? 0;

        $severity = match(true) {
            $mag >= 6.5 => 'red',
            $mag >= 5.0 => 'orange',
            $mag >= 4.0 => 'yellow',
            default     => 'green',
        };

        $alerts[] = [
            'id'           => $feat['id'],
            'type'         => 'earthquake',
            'title'        => 'Earthquake M' . $mag . ' — ' . ($p['place'] ?? 'Near Nepal'),
            'severity'     => $severity,
            'affected_area'=> $p['place'] ?? 'Nepal Region',
            'lat'          => $geo[1] ?? $lat,
            'lon'          => $geo[0] ?? $lon,
            'source'       => 'USGS',
            'source_url'   => $p['url'] ?? '',
            'time'         => date('Y-m-d H:i:s', intval(($p['time'] ?? 0) / 1000)),
            'safety_tips'  => 'Drop, Cover, and Hold On. Move away from windows. Do not use elevators.',
        ];
    }
}

// 2. ReliefWeb
$rwUrl = RELIEFWEB_API
    . '?appname=surakshit_nepal&profile=list&filter[operator]=AND'
    . '&filter[conditions][0][field]=country.iso3&filter[conditions][0][value]=NPL'
    . '&filter[conditions][1][field]=status&filter[conditions][1][value]=ongoing'
    . '&fields[include][]=name&fields[include][]=date&fields[include][]=type'
    . '&fields[include][]=status&limit=5';

$rwRes = http_get($rwUrl, 15);
if ($rwRes['success']) {
    $rw = json_decode($rwRes['body'], true);
    foreach (($rw['data'] ?? []) as $item) {
        $fields = $item['fields'] ?? [];
        $typeName = strtolower($fields['type'][0]['name'] ?? 'other');
        
        $typeMap = ['flood'=>'flood', 'landslide'=>'landslide', 'earthquake'=>'earthquake', 'storm'=>'storm'];
        $alertType = 'other';
        foreach ($typeMap as $k => $v) {
            if (str_contains($typeName, $k)) { $alertType = $v; break; }
        }

        $alerts[] = [
            'id'           => 'rw-' . $item['id'],
            'type'         => $alertType,
            'title'        => $fields['name'] ?? 'Disaster Alert',
            'severity'     => 'orange',
            'affected_area'=> 'Nepal',
            'lat'          => 27.7,
            'lon'          => 85.3,
            'source'       => 'ReliefWeb',
            'source_url'   => 'https://reliefweb.int/disaster/' . $item['id'],
            'time'         => $fields['date']['created'] ?? date('Y-m-d H:i:s'),
            'safety_tips'  => 'Follow official instructions. Evacuate if advised.',
        ];
    }
}

// 3. Database Sync
$db = Database::getInstance();
$insertedCount = 0;

foreach ($alerts as $a) {
    try {
        $stmt = $db->prepare(
            'INSERT IGNORE INTO alerts (alert_id,type,title,severity,affected_area,lat,lon,source,source_url,expected_time,safety_tips)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $a['id'], $a['type'], $a['title'], $a['severity'],
            $a['affected_area'], $a['lat'], $a['lon'],
            $a['source'], $a['source_url'], $a['time'], $a['safety_tips']
        ]);
        if ($stmt->rowCount() > 0) {
            $insertedCount++;
        }
    } catch (Exception $e) { }
}

echo "Fetched " . count($alerts) . " alerts. Inserted $insertedCount new alerts.\n";

// 4. Push Notification Broadcast
$stmt = $db->query("SELECT * FROM alerts WHERE push_sent = 0 AND created_at > (NOW() - INTERVAL 1 DAY)");
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($pending) === 0) {
    echo "No new alerts to push.\n";
    exit;
}

echo "Found " . count($pending) . " new alerts to push!\n";

foreach ($pending as $alert) {
    // Send via OneSignal API
    if (ONESIGNAL_APP_ID === 'YOUR_ONESIGNAL_APP_ID' || ONESIGNAL_REST_API_KEY === 'YOUR_ONESIGNAL_REST_API_KEY') {
        echo "Error: OneSignal not configured. Skipping push for alert ID {$alert['id']}.\n";
        continue;
    }

    $headings = ['en' => '⚠️ ' . ucfirst($alert['type']) . ' Alert'];
    $contents = ['en' => $alert['title'] . "\n" . $alert['safety_tips']];

    $payload = [
        'app_id'            => ONESIGNAL_APP_ID,
        'included_segments' => ['All'],
        'headings'          => $headings,
        'contents'          => $contents,
        'url'               => APP_URL . '/alerts.php',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://onesignal.com/api/v1/notifications');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ' . ONESIGNAL_REST_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "Successfully sent push for: {$alert['title']}\n";
        // Mark as sent
        $db->prepare("UPDATE alerts SET push_sent = 1 WHERE id = ?")->execute([$alert['id']]);
    } else {
        echo "Failed to send push for: {$alert['title']}. Response: $response\n";
    }
}

echo "Cron Job Complete.\n";
