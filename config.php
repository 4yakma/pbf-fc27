
<?php

define('DISCORD_CLIENT_ID', '1399326802433937418');

// Mets ici le vrai Client Secret récupéré sur Discord Developer Portal
define('DISCORD_CLIENT_SECRET', 'ctxT7m6SjoaZ9BYb386am2L8EYiucAkT');

define('DISCORD_REDIRECT_URI', 'http://localhost/discord-callback.php');

define('DISCORD_API', 'https://discord.com/api/v10');

// Configuration des sessions
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Réponse JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Requête vers Discord
function discordRequest($url, $postData = null, $accessToken = null) {

    $ch = curl_init($url);

    $headers = [
        'Accept: application/json'
    ];

    if ($accessToken !== null) {
        $headers[] = 'Authorization: Bearer ' . $accessToken;
    }

    if ($postData !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false
    ]);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        error_log('Discord CURL error: ' . $error);
        return false;
    }

    $decoded = json_decode($response, true);

    if ($status < 200 || $status >= 300) {
        error_log(
            'Discord API HTTP ' . $status .
            ' - Réponse : ' . $response
        );

        return false;
    }

    return $decoded;
}
