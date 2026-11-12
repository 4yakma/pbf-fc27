
<?php

// ==========================================
// CONFIGURATION DISCORD - PROFIL BAS FC
// ==========================================

define('DISCORD_CLIENT_ID', '1399326802433937418');

// Remplace par ton NOUVEAU Client Secret Discord
define('DISCORD_CLIENT_SECRET', 'ctxT7m6SjoaZ9BYb386am2L8EYiucAkT');

define(
    'DISCORD_REDIRECT_URI',
    'http://localhost/discord-callback.php'
);

define('DISCORD_API', 'https://discord.com/api/v10');


// ==========================================
// CONFIGURATION DES SESSIONS
// ==========================================

$isHttps = (
    !empty($_SERVER['HTTPS']) &&
    strtolower((string) $_SERVER['HTTPS']) !== 'off'
);

if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}


// ==========================================
// REPONSE JSON
// ==========================================

function jsonResponse($data, $status = 200)
{
    http_response_code($status);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}


// ==========================================
// REQUETE API DISCORD
// ==========================================

function discordRequest($url, $postData = null, $accessToken = null)
{
    if (!function_exists('curl_init')) {
        error_log('Discord : extension cURL non activée.');

        return [
            '_error' => 'Extension PHP cURL non activée.'
        ];
    }

    $ch = curl_init($url);

    if ($ch === false) {
        return [
            '_error' => 'Impossible de créer la requête cURL.'
        ];
    }

    $headers = [
        'Accept: application/json',
        'User-Agent: PROFIL-BAS-FC/1.0'
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
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    if ($postData !== null) {

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query($postData)
        );
    }

    $response = curl_exec($ch);

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $error = curl_error($ch);

    curl_close($ch);


    // ERREUR CURL

    if ($response === false) {

        error_log('Discord CURL : ' . $error);

        return [
            '_error' => 'Erreur cURL : ' . $error
        ];
    }


    // DECODAGE JSON

    $decoded = json_decode($response, true);

    if (!is_array($decoded)) {

        error_log('Discord : réponse JSON invalide. HTTP ' . $status);

        return [
            '_error' => 'Réponse JSON invalide reçue de Discord.',
            '_http_status' => $status
        ];
    }


    // ERREUR HTTP DISCORD

    if ($status < 200 || $status >= 300) {

        $message = $decoded['error_description']
            ?? $decoded['message']
            ?? $decoded['error']
            ?? 'Erreur inconnue de Discord';

        error_log(
            'Discord API HTTP ' . $status . ' : ' . $message
        );

        return [
            '_error' => $message,
            '_http_status' => $status
        ];
    }

    return $decoded;
}

