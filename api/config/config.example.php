<?php
// ============================================================
// Surakshit Nepal — Central Configuration
// NEVER expose this file publicly. Keep above web root if possible.
// ============================================================

define('APP_NAME',    'Surakshit Nepal');
define('APP_VERSION', '1.0.0');
define('APP_URL',     'http://localhost/weather');
define('APP_ENV',     'development'); // 'production' on live server

// ----------------------------------------------------------
// Database
// ----------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'surakshit_nepal');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ----------------------------------------------------------
// Weather APIs
// ----------------------------------------------------------
define('OPENWEATHER_API_KEY', 'YOUR_OPENWEATHER_API_KEY');
define('OPENWEATHER_BASE',    'https://api.openweathermap.org/data/2.5');
define('OPENWEATHER_GEO',     'https://api.openweathermap.org/geo/1.0');
define('OPENWEATHER_AQI',     'https://api.openweathermap.org/data/2.5/air_pollution');

define('WEATHERAPI_KEY',  'YOUR_WEATHERAPI_KEY');
define('WEATHERAPI_BASE', 'https://api.weatherapi.com/v1');

define('OPEN_METEO_BASE', 'https://api.open-meteo.com/v1/forecast');

// ----------------------------------------------------------
// Maps
// ----------------------------------------------------------
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');

// ----------------------------------------------------------
// Disaster APIs (free / no key needed)
// ----------------------------------------------------------
define('USGS_EARTHQUAKE_API', 'https://earthquake.usgs.gov/fdsnws/event/1/query');
define('GDACS_API',            'https://www.gdacs.org/xml/rss.xml');
define('RELIEFWEB_API',        'https://api.reliefweb.int/v1/disasters');

// ----------------------------------------------------------
// AI — Google Gemini
// ----------------------------------------------------------
define('GEMINI_API_KEY',   'YOUR_GEMINI_API_KEY');
define('GEMINI_MODEL',     'gemini-1.5-flash');
define('GEMINI_API_BASE',  'https://generativelanguage.googleapis.com/v1beta/models');

// ----------------------------------------------------------
// Push Notifications — OneSignal
// ----------------------------------------------------------
define('ONESIGNAL_APP_ID',       'YOUR_ONESIGNAL_APP_ID');
define('ONESIGNAL_REST_API_KEY', 'YOUR_ONESIGNAL_REST_API_KEY');

// ----------------------------------------------------------
// Cache TTLs (seconds)
// ----------------------------------------------------------
define('CACHE_WEATHER_TTL',   15 * 60);  // 15 minutes
define('CACHE_ALERTS_TTL',    30 * 60);  // 30 minutes
define('CACHE_AI_TTL',        60 * 60);  // 1 hour

// ----------------------------------------------------------
// Nepal bounding box (for filtering APIs)
// ----------------------------------------------------------
define('NEPAL_LAT_MIN', 26.347);
define('NEPAL_LAT_MAX', 30.447);
define('NEPAL_LON_MIN', 80.058);
define('NEPAL_LON_MAX', 88.201);

// ----------------------------------------------------------
// Security
// ----------------------------------------------------------
define('CSRF_SECRET', 'change_this_to_a_random_64char_string_xyz987');

// ----------------------------------------------------------
// Error handling
// ----------------------------------------------------------
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set('Asia/Kathmandu');

// ----------------------------------------------------------
// Helper: send JSON response and exit
// ----------------------------------------------------------
function json_response(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ----------------------------------------------------------
// Helper: sanitize input
// ----------------------------------------------------------
function sanitize_string(string $input, int $maxLen = 255): string {
    return htmlspecialchars(substr(trim(strip_tags($input)), 0, $maxLen), ENT_QUOTES, 'UTF-8');
}

function sanitize_float(mixed $value, float $default = 0.0): float {
    $f = filter_var($value, FILTER_VALIDATE_FLOAT);
    return ($f === false) ? $default : $f;
}
