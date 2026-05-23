<?php
// config/google_config.php

// Carga la librería que instalaste con Composer
require_once __DIR__ . '/../vendor/autoload.php';

// ----------------------------------------------------
// TUS CLAVES DE GOOGLE
// ----------------------------------------------------
$clientID = 'TU_CLIENT_ID_DE_GOOGLE_AQUI';
$clientSecret = 'TU_CLIENT_SECRET_AQUI';
$redirectUri = 'http://localhost/TFG/callback.php';

// Crear Cliente de Google
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
?>