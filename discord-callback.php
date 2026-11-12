
<?php

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DIAGNOSTIC DE LA SESSION OAUTH
error_log('CALLBACK SESSION ID : ' . session_id());
error_log('CALLBACK COOKIE PRESENT : ' . (isset($_COOKIE[session_name()]) ? 'OUI' : 'NON'));
error_log('CALLBACK STATE PRESENT : ' . (isset($_SESSION['discord_oauth_state']) ? 'OUI' : 'NON'));

// Vérification d'une erreur renvoyée par Discord
if (isset($_GET['error'])) {
    http_response_code(400);
    exit('Connexion Discord refusée : ' . htmlspecialchars($_GET['error']));
}

// Récupération du code et du state
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';
$expectedState = $_SESSION['discord_oauth_state'] ?? '';

// DIAGNOSTIC : COMPARAISON DES STATES
error_log('CALLBACK STATE MATCH : ' . (
    is_string($state) &&
    is_string($expectedState) &&
    $state !== '' &&
    hash_equals($expectedState, $state)
    ? 'OUI'
    : 'NON'
));

// Vérification de sécurité OAuth
if (
    !is_string($code) ||
    !is_string($state) ||
    !is_string($expectedState) ||
    $code === '' ||
    $expectedState === '' ||
    !hash_equals($expectedState, $state)
) {
    http_response_code(403);
    exit('Erreur de sécurité OAuth.');
}

unset($_SESSION['discord_oauth_state']);

// Échange du code contre un token Discord
$token = discordRequest(
    'https://discord.com/api/v10/oauth2/token',
    [
        'client_id' => DISCORD_CLIENT_ID,
        'client_secret' => DISCORD_CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => DISCORD_REDIRECT_URI
    ]
);

// Vérification du token
if (!$token || !is_array($token) || empty($token['access_token'])) {

    http_response_code(500);

    echo '<h2>Erreur lors de la récupération du token Discord</h2>';
    echo '<pre>';

    if (is_array($token)) {
        unset($token['access_token'], $token['refresh_token']);
        print_r($token);
    } else {
        echo 'Discord n’a pas retourné de réponse valide.';
        echo "\nVérifie le Client Secret, le Redirect URI et cURL.";
    }

    echo '</pre>';
    exit;
}

// Récupération du profil Discord
$user = discordRequest(
    DISCORD_API . '/users/@me',
    null,
    $token['access_token']
);

if (!$user || !is_array($user) || empty($user['id'])) {
    http_response_code(500);
    exit('Impossible de récupérer le profil Discord.');
}

// Création de l'avatar
if (!empty($user['avatar'])) {

    $extension = str_starts_with($user['avatar'], 'a_')
        ? 'gif'
        : 'png';

    $avatar = 'https://cdn.discordapp.com/avatars/' .
        $user['id'] . '/' .
        $user['avatar'] . '.' .
        $extension;

} else {

    $avatar = 'https://cdn.discordapp.com/embed/avatars/0.png';
}

// Renouvellement de la session
session_regenerate_id(true);

// Enregistrement du joueur
$_SESSION['discord_user'] = [
    'id' => $user['id'],
    'username' => $user['username'] ?? '',
    'global_name' => $user['global_name'] ?? null,
    'avatar' => $avatar
];

// Retour à l'accueil
header('Location: index.html');
exit;
