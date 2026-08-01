<?php
// ============================================================
// Surakshit Nepal — Helper Functions
// ============================================================

require_once __DIR__ . '/../api/config/config.php';

// ----------------------------------------------------------
// CSRF
// ----------------------------------------------------------
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// ----------------------------------------------------------
// HTTP — make a GET request with curl
// ----------------------------------------------------------
function http_get(string $url, int $timeout = 10): array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'SurakshitNepal/1.0',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body  = curl_exec($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) return ['success' => false, 'error' => $error, 'code' => 0];
    if ($code < 200 || $code >= 300) return ['success' => false, 'error' => "HTTP $code", 'code' => $code];
    return ['success' => true, 'body' => $body, 'code' => $code];
}

// ----------------------------------------------------------
// Weather helpers
// ----------------------------------------------------------
function uv_label(float $uv): string {
    return match(true) {
        $uv < 3  => 'Low',
        $uv < 6  => 'Moderate',
        $uv < 8  => 'High',
        $uv < 11 => 'Very High',
        default  => 'Extreme',
    };
}

function aqi_label(int $aqi): string {
    return match($aqi) {
        1 => 'Good',
        2 => 'Moderate',
        3 => 'Unhealthy for sensitive groups',
        4 => 'Unhealthy',
        5 => 'Very Unhealthy',
        6 => 'Hazardous',
        default => 'Unknown',
    };
}

function wind_direction(int $deg): string {
    $dirs = ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
    return $dirs[round($deg / 22.5) % 16];
}

function celsius_to_fahrenheit(float $c): float {
    return round($c * 9 / 5 + 32, 1);
}

function ms_to_kmh(float $ms): float {
    return round($ms * 3.6, 1);
}

// ----------------------------------------------------------
// Risk level from weather data
// ----------------------------------------------------------
function calculate_risk_level(array $weather): string {
    $rain  = $weather['rain_1h']   ?? 0;
    $wind  = $weather['wind_speed'] ?? 0;   // m/s
    $temp  = $weather['temp']       ?? 20;

    if ($rain > 50 || $wind > 20 || $temp > 40 || $temp < 0) return 'high';
    if ($rain > 20 || $wind > 13 || $temp > 35 || $temp < 5) return 'medium';
    if ($rain > 5  || $wind > 8)                              return 'low';
    return 'safe';
}

// ----------------------------------------------------------
// Location key (truncate lat/lon to 2 decimal places)
// ----------------------------------------------------------
function location_key(float $lat, float $lon): string {
    return number_format($lat, 2, '.', '') . '_' . number_format($lon, 2, '.', '');
}

// ----------------------------------------------------------
// Validate Nepal bounding box
// ----------------------------------------------------------
function is_in_nepal(float $lat, float $lon): bool {
    return ($lat >= NEPAL_LAT_MIN && $lat <= NEPAL_LAT_MAX &&
            $lon >= NEPAL_LON_MIN && $lon <= NEPAL_LON_MAX);
}

// ----------------------------------------------------------
// Format UNIX timestamp to readable
// ----------------------------------------------------------
function format_time(int $ts, string $format = 'H:i'): string {
    return date($format, $ts);
}

// ----------------------------------------------------------
// XSS-safe output
// ----------------------------------------------------------
function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
