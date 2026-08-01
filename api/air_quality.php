<?php
// ============================================================
// Surakshit Nepal — Air Quality API
// GET /api/air_quality.php?lat=27.7&lon=85.3
// ============================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../functions/helpers.php';

$lat = sanitize_float($_GET['lat'] ?? 0);
$lon = sanitize_float($_GET['lon'] ?? 0);

if ($lat === 0.0 && $lon === 0.0) {
    json_response(['error' => 'Invalid coordinates'], 400);
}

$url = WEATHERAPI_BASE . '/current.json?key=' . WEATHERAPI_KEY . '&q=' . $lat . ',' . $lon . '&aqi=yes';
$res = http_get($url);

if (!$res['success']) {
    json_response(['error' => 'AQI unavailable'], 503);
}

$raw  = json_decode($res['body'], true);
$aqi  = $raw['current']['air_quality']['us-epa-index'] ?? 1;
$comp = $raw['current']['air_quality'] ?? [];

json_response([
    'aqi'        => $aqi,
    'aqi_label'  => aqi_label($aqi),
    'pm2_5'      => round($comp['pm2_5'] ?? 0, 2),
    'pm10'       => round($comp['pm10']  ?? 0, 2),
    'no2'        => round($comp['no2']   ?? 0, 2),
    'o3'         => round($comp['o3']    ?? 0, 2),
    'co'         => round($comp['co']    ?? 0, 2),
    'so2'        => round($comp['so2']   ?? 0, 2),
]);
