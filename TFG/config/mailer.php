<?php
// config/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// =========================================================================
// FUNCIÓN AUXILIAR: CONFIGURACIÓN BASE (Para no repetir código)
// =========================================================================
function configurarSMTP($mail) {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'notjoseda@gmail.com';
    $mail->Password   = 'contraseña'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // IMPORTANTE: Ignorar errores de certificado en localhost
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Si quieres ver el error real, descomenta la siguiente línea:
    // $mail->SMTPDebug = 2; 
}

// =========================================================================
// FUNCIÓN 1: CORREO DE BIENVENIDA
// =========================================================================
function enviarCorreoBienvenida($emailDestino, $nombreDestino) {
    $mail = new PHPMailer(true);
    try {
        configurarSMTP($mail);
        $mail->setFrom('notjoseda@gmail.com', 'Academia Terra');
        $mail->addAddress($emailDestino, $nombreDestino);
        $mail->isHTML(true);
        $mail->Subject = '¡Bienvenido/a a Academia Terra! 🎓';
        
        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #3b82f6; text-align: center;'>¡Hola, $nombreDestino!</h2>
                <p>Nos hace muchísima ilusión darte la bienvenida a <b>Academia Terra</b>.</p>
                <p>Tu cuenta ha sido creada con éxito. Ya puedes acceder a tu panel de alumno para ver la información de tus cursos.</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/TFG/login.php' style='background-color: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ir a mi Panel</a>
                </p>
                <hr style='border: 0; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #777; text-align: center;'>Este es un correo automático, por favor no respondas.</p>
            </div>
        ";
        $mail->Body = $cuerpo;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error Mailer (Bienvenida): " . $mail->ErrorInfo);
        return false;
    }
}

// =========================================================================
// FUNCIÓN 2: CORREO DE RECUPERACIÓN
// =========================================================================
function enviarCorreoRecuperacion($emailDestino, $nombreDestino, $token) {
    $mail = new PHPMailer(true);
    try {
        configurarSMTP($mail);
        $mail->setFrom('notjoseda@gmail.com', 'Soporte Academia Terra');
        $mail->addAddress($emailDestino, $nombreDestino);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperar Contraseña - Academia Terra 🔑';
        $enlace = "http://localhost/TFG/nueva_pass.php?token=" . $token;

        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                <h2 style='color: #3b82f6; text-align: center;'>Recuperación de contraseña</h2>
                <p>Hola, $nombreDestino. Haz clic para restablecer tu acceso:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='$enlace' style='background-color: #ef4444; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Restablecer Contraseña</a>
                </p>
                <hr style='border: 0; border-top: 1px solid #eee;'>
            </div>
        ";
        $mail->Body = $cuerpo;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error Mailer (Recuperación): " . $mail->ErrorInfo);
        return false;
    }
}

// =========================================================================
// FUNCIÓN 3: CORREO DE MATRÍCULA 🚀
// =========================================================================
function enviarCorreoMatricula($emailDestino, $nombreDestino, $cursoComprado) {
    $mail = new PHPMailer(true);
    try {
        configurarSMTP($mail);
        $mail->setFrom('notjoseda@gmail.com', 'Matriculaciones Academia Terra');
        $mail->addAddress($emailDestino, $nombreDestino);
        $mail->isHTML(true);
        $mail->Subject = "¡Confirmación de Matrícula en $cursoComprado! 🚀";
        
        $cuerpo = "
            <div style='font-family: Arial, sans-serif; background-color: #0a0f1c; color: #e0e7ff; padding: 40px; border-radius: 10px; max-width: 600px; margin: 0 auto; border: 1px solid #00d4ff;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #00d4ff; margin: 0;'>¡Ya eres alumno de Terra!</h1>
                </div>
                <p style='font-size: 16px;'>Hola, <strong>$nombreDestino</strong>:</p>
                <p style='font-size: 16px;'>Ya tienes acceso total al ciclo formativo:</p>
                <div style='background: rgba(0, 212, 255, 0.1); border: 1px dashed #00d4ff; border-radius: 8px; padding: 20px; text-align: center; margin: 25px 0;'>
                    <h2 style='color: #00d4ff; margin: 0; font-size: 24px;'>$cursoComprado</h2>
                </div>
                <div style='text-align: center; margin: 40px 0;'>
                    <a href='http://localhost/TFG/login.php' style='background-color: #00d4ff; color: #0a0f1c; padding: 15px 35px; text-decoration: none; border-radius: 8px; font-weight: 800; font-size: 18px;'>EMPEZAR AHORA</a>
                </div>
            </div>
        ";
        $mail->Body = $cuerpo;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error Mailer (Matrícula): " . $mail->ErrorInfo);
        return false;
    }
}
?>