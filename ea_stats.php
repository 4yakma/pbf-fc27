
<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$url = 'https://proclubs.ea.com/api/fc/allTimeLeaderboard/search?' . http_build_query([
    'platform' => 'common-gen5',
    'clubName' => 'PROFIL BAS FC',
    'maxResultCount' => 100
]);

function sendError($message, $status = 500, $details = []) {
    http_response_code($status);
    echo json_encode(array_merge([
        'error' => $message
    ], $details), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!function_exists('curl_init')) {
    sendError('Extension PHP cURL non activée.');
}

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_ENCODING => '',
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/plain, */*',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        'Origin: https://www.ea.com',
        'Referer: https://www.ea.com/'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($response === false) {
    sendError('Impossible de contacter EA.', 502, [
        'curl_error' => $curlError
    ]);
}

if ($httpCode !== 200) {
    sendError('EA a refusé la requête.', 502, [
        'ea_http_status' => $httpCode,
        'ea_response' => substr($response, 0, 1000)
    ]);
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    sendError('Réponse JSON EA invalide.', 502, [
        'json_error' => json_last_error_msg()
    ]);
}

echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
