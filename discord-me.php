
<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['discord_user'])) {
    jsonResponse([
        'loggedIn' => false
    ]);
}

$user = $_SESSION['discord_user'];

jsonResponse([
    'loggedIn' => true,
    'avatar' => $user['avatar'] ?? '',
    'displayName' => $user['global_name'] ?: ($user['username'] ?? 'Joueur')
]);
