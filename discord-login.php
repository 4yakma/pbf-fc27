
<?php

require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Création du code de sécurité OAuth
$state = bin2hex(random_bytes(32));

// Enregistrement dans la session
$_SESSION['discord_oauth_state'] = $state;

// Sauvegarde immédiate de la session
session_write_close();

// Paramètres Discord
$params = [
    'client_id' => DISCORD_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => DISCORD_REDIRECT_URI,
    'scope' => 'identify',
    'state' => $state
];

// Redirection vers Discord
$url = 'https://discord.com/oauth2/authorize?' .
       http_build_query($params);

header('Location: ' . $url);
exit;
