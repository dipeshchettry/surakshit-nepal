<?php
// ============================================================
// Surakshit Nepal — AI Safety Guidance (Gemini API)
// POST /api/ai_safety.php
// Body: { lat, lon, weather: {...}, risk_level: "medium" }
// ============================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/../functions/helpers.php';

$body    = json_decode(file_get_contents('php://input'), true) ?? [];
$lat     = sanitize_float($body['lat'] ?? ($_GET['lat'] ?? 27.7));
$lon     = sanitize_float($body['lon'] ?? ($_GET['lon'] ?? 85.3));
$weather = $body['weather'] ?? [];
$risk    = in_array($body['risk_level'] ?? 'safe', ['safe','low','medium','high'])
           ? ($body['risk_level'] ?? 'safe') : 'safe';

$locKey  = location_key($lat, $lon);

// --- Check AI cache (1 hour) ---
try {
    $db   = Database::getInstance();
    $stmt = $db->prepare('SELECT response, risk_level FROM ai_logs WHERE location_key = ? AND cached_until > NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute([$locKey]);
    $cached = $stmt->fetch();
    if ($cached) {
        json_response(['cached' => true, 'risk_level' => $cached['risk_level'], 'advice' => json_decode($cached['response'], true)]);
    }
} catch (Exception $e) {}

// --- Build Gemini prompt ---
$temp     = $weather['temp']         ?? '?';
$humidity = $weather['humidity']     ?? '?';
$wind     = $weather['wind_speed']   ?? '?';
$desc     = $weather['weather_desc'] ?? 'unknown';
$rain     = $weather['rain_1h']      ?? 0;

$prompt = <<<PROMPT
You are Surakshit Nepal AI — a disaster safety assistant for Nepal.

Current weather at ({$lat}, {$lon}):
- Temperature: {$temp}°C
- Humidity: {$humidity}%
- Wind Speed: {$wind} m/s
- Description: {$desc}
- Rain last hour: {$rain} mm
- Risk Level: {$risk}

Respond ONLY with a JSON object (no markdown, no explanation outside JSON):
{
  "risk_level": "safe|low|medium|high",
  "summary_en": "2-3 sentence weather summary in plain English for Nepal residents",
  "summary_ne": "2-3 sentence weather summary in Nepali (Devanagari script)",
  "advice_en": ["tip 1 in English", "tip 2", "tip 3"],
  "advice_ne": ["tip 1 in Nepali", "tip 2", "tip 3"],
  "emergency_action": "What to do RIGHT NOW if risk is high (1 sentence, empty string if safe)",
  "icon": "one of: shield-check, exclamation-triangle, exclamation-circle, times-circle"
}
PROMPT;

// --- Call Gemini API ---
$endpoint = GEMINI_API_BASE . '/' . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

$payload = json_encode([
    'contents' => [[
        'parts' => [['text' => $prompt]]
    ]],
    'generationConfig' => [
        'temperature'     => 0.3,
        'maxOutputTokens' => 800,
        'responseMimeType'=> 'application/json',
    ]
]);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 20,
]);
$geminiBody = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$geminiBody) {
    // Fallback advice
    $fallback = build_fallback_advice($risk, $desc);
    json_response(['cached' => false, 'risk_level' => $risk, 'advice' => $fallback, 'ai_used' => false]);
}

$gemini   = json_decode($geminiBody, true);
$text     = $gemini['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
$advice   = json_decode($text, true);

if (!$advice || !isset($advice['risk_level'])) {
    $fallback = build_fallback_advice($risk, $desc);
    json_response(['cached' => false, 'risk_level' => $risk, 'advice' => $fallback, 'ai_used' => false]);
}

$tokens = $gemini['usageMetadata']['totalTokenCount'] ?? 0;

// --- Cache AI response ---
try {
    $db   = Database::getInstance();
    $stmt = $db->prepare(
        'INSERT INTO ai_logs (location_key, prompt, response, risk_level, model, tokens_used, cached_until)
         VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        $locKey, $prompt, json_encode($advice), $advice['risk_level'] ?? $risk,
        GEMINI_MODEL, $tokens, date('Y-m-d H:i:s', time() + CACHE_AI_TTL)
    ]);
} catch (Exception $e) {}

json_response(['cached' => false, 'risk_level' => $advice['risk_level'] ?? $risk, 'advice' => $advice, 'ai_used' => true]);

// ----------------------------------------------------------
// Fallback (no API key or Gemini down)
// ----------------------------------------------------------
function build_fallback_advice(string $risk, string $desc): array {
    $adviceMap = [
        'safe'   => [
            'summary_en' => "Weather conditions in your area are currently safe. Enjoy the pleasant weather while staying prepared.",
            'summary_ne' => "तपाईंको क्षेत्रमा हालको मौसम सुरक्षित छ। सुखद मौसमको आनन्द लिँदै तयार रहनुहोस्।",
            'advice_en'  => ["Stay hydrated and carry water.", "Good time for outdoor activities.", "Monitor weather updates periodically."],
            'advice_ne'  => ["पानी पिइरहनुहोस्।", "बाहिरी गतिविधिका लागि राम्रो समय।", "मौसम अपडेट नियमित जाँच गर्नुहोस्।"],
            'emergency_action' => '',
            'icon'       => 'shield-check',
        ],
        'low'    => [
            'summary_en' => "Mild weather changes detected. Stay alert and avoid unnecessary outdoor exposure.",
            'summary_ne' => "सामान्य मौसम परिवर्तन देखिएको छ। सतर्क रहनुहोस्।",
            'advice_en'  => ["Carry an umbrella.", "Avoid low-lying areas.", "Keep emergency contacts ready."],
            'advice_ne'  => ["छाता लिएर हिँड्नुहोस्।", "सखाप क्षेत्रबाट टाढा रहनुहोस्।", "आपतकालीन नम्बर तयार राख्नुहोस्।"],
            'emergency_action' => '',
            'icon'       => 'exclamation-triangle',
        ],
        'medium' => [
            'summary_en' => "Moderate risk conditions. Avoid travel in flood or landslide-prone areas.",
            'summary_ne' => "मध्यम जोखिम अवस्था। बाढी वा पहिरो जाने क्षेत्रमा यात्रा नगर्नुहोस्।",
            'advice_en'  => ["Stay indoors if possible.", "Keep emergency kit ready.", "Monitor NDRRMA updates."],
            'advice_ne'  => ["सम्भव भए घरभित्र रहनुहोस्।", "आपतकालीन किट तयार राख्नुहोस्।", "NDRRMA अपडेट हेर्नुहोस्।"],
            'emergency_action' => 'Prepare to evacuate if instructed by local authorities.',
            'icon'       => 'exclamation-circle',
        ],
        'high'   => [
            'summary_en' => "HIGH RISK conditions detected! Immediately move to higher ground and follow evacuation orders.",
            'summary_ne' => "उच्च जोखिम अवस्था! तुरुन्त उच्च स्थानमा जानुहोस् र निकासी आदेश पालना गर्नुहोस्।",
            'advice_en'  => ["Evacuate immediately if ordered.", "Call emergency services: 1149", "Do not cross flooded rivers."],
            'advice_ne'  => ["आदेश भए तुरुन्त स्थानान्तरण गर्नुहोस्।", "आपतकालीन सेवा कल गर्नुहोस्: ११४९", "बाढी आएको नदी नतर्नुहोस्।"],
            'emergency_action' => 'CALL 1149 (NDRRMA) or 100 (Police) immediately!',
            'icon'       => 'times-circle',
        ],
    ];
    return $adviceMap[$risk] ?? $adviceMap['safe'];
}
