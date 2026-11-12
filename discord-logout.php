
<?php

require_once __DIR__ . '/config.php';

// Vider les données de connexion
$_SESSION = [];

// Détruire la session
session_destroy();

// Retour à l'accueil du site
header('Location: index.html');
exit;
