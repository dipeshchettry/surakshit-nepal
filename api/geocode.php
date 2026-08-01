<?php
// ============================================================
// Surakshit Nepal — Reverse Geocode
// GET /api/geocode.php?lat=27.7&lon=85.3
// ============================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../functions/helpers.php';

$lat = sanitize_float($_GET['lat'] ?? 27.7);
$lon = sanitize_float($_GET['lon'] ?? 85.3);

$url = OPENWEATHER_GEO . '/reverse?lat=' . $lat . '&lon=' . $lon . '&limit=1&appid=' . OPENWEATHER_API_KEY;
$res = http_get($url);

if (!$res['success']) {
    json_response(['city' => 'Nepal', 'country' => 'NP', 'state' => '']);
}

$data = json_decode($res['body'], true);
$loc  = $data[0] ?? [];

json_response([
    'city'    => $loc['local_names']['en'] ?? $loc['name'] ?? 'Nepal',
    'state'   => $loc['state']   ?? '',
    'country' => $loc['country'] ?? 'NP',
    'lat'     => $loc['lat']     ?? $lat,
    'lon'     => $loc['lon']     ?? $lon,
]);
