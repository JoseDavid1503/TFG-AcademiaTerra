<?php
session_start();
require_once 'config/config.php';
require_once 'config/db_pdo.php';
require_once 'config/google_config.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if(!isset($token['error'])){
        $client->setAccessToken($token['access_token']);
        
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $google_id = $google_account_info->id;
        $picture = $google_account_info->picture; // NUEVO: Cogemos la foto

        $db = DB::open();
        
        $sql = "SELECT * FROM Alumnos WHERE email = ? LIMIT 1";
        $usuario = $db->query($sql, [$email]);

        if ($usuario) {
            $_SESSION['user_id'] = $usuario[0]['id'];
            $_SESSION['tipo_usuario'] = 'alumno';
            $_SESSION['nombre'] = $usuario[0]['nombre'];
            $_SESSION['foto'] = $picture; // NUEVO: Guardamos la foto en la sesión
            
            // Actualizamos la foto en la base de datos para tenerla siempre
            $sql_update = "UPDATE Alumnos SET google_id = ?, foto = ? WHERE id = ?";
            $db->query($sql_update, [$google_id, $picture, $usuario[0]['id']]);
            
            header('Location: plataforma/alumno/index.php');
            exit;
        } else {
            $_SESSION['error_login'] = "Ese correo de Google no está registrado.";
            header('Location: login.php');
            exit;
        }
    }
}

header('Location: login.php');
exit;
?>