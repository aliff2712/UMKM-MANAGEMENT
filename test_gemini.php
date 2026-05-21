<?php

// Load .env manual
$envFile = __DIR__ . '/.env';
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value);
}

$apiKey = $env['GEMINI_API_KEY'] ?? null;

echo "=== DEBUG GEMINI API ===" . PHP_EOL;
echo "API Key: " . ($apiKey ? substr($apiKey, 0, 15) . '...' : 'TIDAK ADA!') . PHP_EOL;
echo PHP_EOL;

if (!$apiKey) {
    echo "ERROR: GEMINI_API_KEY tidak ditemukan di .env" . PHP_EOL;
    exit(1);
}

/**
 * Model aktif per Mei 2026:
 *  ✅ gemini-2.5-flash-lite  → free tier 15 RPM / 1.000 RPD (paling hemat)
 *  ✅ gemini-2.5-flash       → free tier 10 RPM / 250 RPD
 *  ✅ gemini-2.5-pro         → free tier  5 RPM / 100 RPD
 *
 * Model yang SUDAH PENSIUN (jangan dipakai):
 *  ❌ gemini-2.0-flash        → retired 3 Maret 2026
 *  ❌ gemini-2.0-flash-lite   → retired 3 Maret 2026
 *  ❌ gemini-1.5-flash        → deprecated
 *  ❌ gemini-1.5-flash-latest → deprecated
 *  ❌ gemini-pro              → deprecated
 */
$models = [
    'gemini-2.5-flash-lite',
    'gemini-2.5-flash',
    'gemini-2.5-pro',
];

foreach ($models as $model) {
    echo "Testing model: {$model}" . PHP_EOL;

    $url     = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    $payload = json_encode([
        'contents' => [['parts' => [['text' => 'halo, siapa kamu?']]]]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result    = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "  HTTP Code: {$httpCode}" . PHP_EOL;

    if ($curlError) {
        echo "  cURL Error: {$curlError}" . PHP_EOL;
        echo PHP_EOL;
        continue;
    }

    $json = json_decode($result, true);

    if ($httpCode === 200) {
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'N/A';
        echo "  ✅ SUKSES! Respons: " . substr($text, 0, 100) . PHP_EOL;
    } elseif ($httpCode === 429) {
        $errMsg = $json['error']['message'] ?? 'Quota habis';
        echo "  ⚠️  QUOTA HABIS (429): " . substr($errMsg, 0, 200) . PHP_EOL;
        echo "      → Model ini aktif tapi quota free tier hari ini sudah habis." . PHP_EOL;
        echo "      → Tunggu reset quota (tiap hari tengah malam UTC) atau upgrade ke paid tier." . PHP_EOL;
    } elseif ($httpCode === 404) {
        $errMsg = $json['error']['message'] ?? 'Model tidak ditemukan';
        echo "  ❌ MODEL TIDAK DITEMUKAN (404): " . substr($errMsg, 0, 200) . PHP_EOL;
    } elseif ($httpCode === 403) {
        $errMsg = $json['error']['message'] ?? 'Akses ditolak';
        echo "  ❌ API KEY TIDAK VALID / AKSES DITOLAK (403): " . substr($errMsg, 0, 200) . PHP_EOL;
    } else {
        $errMsg = $json['error']['message'] ?? $result;
        echo "  ❌ GAGAL (HTTP {$httpCode}): " . substr($errMsg, 0, 200) . PHP_EOL;
    }

    echo PHP_EOL;
}

echo "=== SELESAI ===" . PHP_EOL;