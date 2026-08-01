<?php
// ============================================================
// Surakshit Nepal — Weather API Endpoint
// GET /api/weather.php?lat=27.7&lon=85.3&units=metric
// ============================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

// --- Validate input ---
$lat   = sanitize_float($_GET['lat']   ?? 0);
$lon   = sanitize_float($_GET['lon']   ?? 0);
$units = in_array($_GET['units'] ?? 'metric', ['metric','imperial']) ? ($_GET['units'] ?? 'metric') : 'metric';

if ($lat === 0.0 && $lon === 0.0) {
    json_response(['error' => 'Invalid coordinates'], 400);
}

$locKey = location_key($lat, $lon);

// --- Check cache ---
try {
    $db  = Database::getInstance();
    $sql = 'SELECT data, expires_at FROM cached_weather WHERE location_key = ? AND expires_at > NOW() ORDER BY cached_at DESC LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->execute([$locKey]);
    $cached = $stmt->fetch();

    if ($cached) {
        $data = json_decode($cached['data'], true);
        $data['cached'] = true;
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
} catch (Exception $e) {
    // DB not available — continue without cache
}

// --- Fetch from WeatherAPI ---
// We use WeatherAPI for highly accurate real-time data including current, forecast, and UV
$weatherApiUrl = WEATHERAPI_BASE . '/forecast.json?key=' . WEATHERAPI_KEY . '&q=' . $lat . ',' . $lon . '&days=7&aqi=yes';
$weatherApiRes = http_get($weatherApiUrl);

if (!$weatherApiRes['success']) {
    json_response(['error' => 'Weather API unavailable', 'detail' => $weatherApiRes['error']], 503);
}

$w = json_decode($weatherApiRes['body'], true);

// --- Build unified response ---
$c = $w['current'] ?? [];
$f = $w['forecast']['forecastday'] ?? [];

$data = [
    'cached'       => false,
    'location'     => [
        'name'     => $w['location']['name'] ?? 'Unknown',
        'country'  => $w['location']['country'] ?? 'NP',
        'lat'      => $lat,
        'lon'      => $lon,
    ],
    'current'      => [
        'temp'         => round($c['temp_c'] ?? 0, 1),
        'feels_like'   => round($c['feelslike_c'] ?? 0, 1),
        'temp_min'     => round($f[0]['day']['mintemp_c'] ?? 0, 1),
        'temp_max'     => round($f[0]['day']['maxtemp_c'] ?? 0, 1),
        'humidity'     => $c['humidity'] ?? 0,
        'pressure'     => $c['pressure_mb'] ?? 0,
        'visibility'   => $c['vis_km'] ?? 10,
        'wind_speed'   => round(($c['wind_kph'] ?? 0) / 3.6, 1), // convert km/h to m/s
        'wind_deg'     => $c['wind_degree'] ?? 0,
        'wind_dir'     => $c['wind_dir'] ?? 'N',
        'clouds'       => $c['cloud'] ?? 0,
        'rain_1h'      => $c['precip_mm'] ?? 0,
        'weather_id'   => $c['condition']['code'] ?? 1000,
        'weather_main' => $c['condition']['text'] ?? 'Clear',
        'weather_desc' => ucfirst($c['condition']['text'] ?? 'Clear sky'),
        'weather_icon' => '01d', // placeholder for frontend fallback
        'weather_icon_url' => isset($c['condition']['icon']) ? 'https:' . $c['condition']['icon'] : null,
        'sunrise'      => isset($f[0]['astro']['sunrise']) ? strtotime(date('Y-m-d') . ' ' . $f[0]['astro']['sunrise']) : time(),
        'sunset'       => isset($f[0]['astro']['sunset']) ? strtotime(date('Y-m-d') . ' ' . $f[0]['astro']['sunset']) : time(),
        'dt'           => $w['location']['localtime_epoch'] ?? time(),
    ],
    'risk_level'   => calculate_risk_level([
        'rain_1h'    => $c['precip_mm'] ?? 0,
        'wind_speed' => round(($c['wind_kph'] ?? 0) / 3.6, 1),
        'temp'       => $c['temp_c'] ?? 20,
    ]),
    'hourly'       => [],
    'daily'        => [],
    'uv_index'     => $c['uv'] ?? null,
];

// Build hourly (next 24 hours)
$currentEpoch = time();
$hourlyCollected = 0;
foreach ($f as $day) {
    if (isset($day['hour'])) {
        foreach ($day['hour'] as $hour) {
            if ($hour['time_epoch'] >= $currentEpoch - 3600 && $hourlyCollected < 24) {
                $data['hourly'][] = [
                    'time'     => date('Y-m-d\TH:i:s', $hour['time_epoch']),
                    'temp'     => $hour['temp_c'] ?? null,
                    'precip_p' => $hour['chance_of_rain'] ?? 0,
                    'code'     => $hour['condition']['code'] ?? 0,
                    'wind'     => round(($hour['wind_kph'] ?? 0) / 3.6, 1),
                    'icon_url' => isset($hour['condition']['icon']) ? 'https:' . $hour['condition']['icon'] : null
                ];
                $hourlyCollected++;
            }
        }
    }
}

// Build daily
foreach ($f as $day) {
    $data['daily'][] = [
        'date'     => $day['date'] ?? '',
        'max'      => $day['day']['maxtemp_c'] ?? null,
        'min'      => $day['day']['mintemp_c'] ?? null,
        'precip'   => $day['day']['totalprecip_mm'] ?? 0,
        'code'     => $day['day']['condition']['code'] ?? 0,
        'uv'       => $day['day']['uv'] ?? 0,
        'sunrise'  => $day['astro']['sunrise'] ?? '',
        'sunset'   => $day['astro']['sunset'] ?? '',
        'icon_url' => isset($day['day']['condition']['icon']) ? 'https:' . $day['day']['condition']['icon'] : null
    ];
}

// --- Store in cache ---
try {
    $db  = Database::getInstance();
    $exp = date('Y-m-d H:i:s', time() + CACHE_WEATHER_TTL);
    $del = $db->prepare('DELETE FROM cached_weather WHERE location_key = ?');
    $del->execute([$locKey]);
    $ins = $db->prepare('INSERT INTO cached_weather (lat,lon,location_key,data,source,expires_at) VALUES (?,?,?,?,?,?)');
    $ins->execute([$lat, $lon, $locKey, json_encode($data), 'weatherapi', $exp]);
} catch (Exception $e) {
    // Cache write failure is non-fatal
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
