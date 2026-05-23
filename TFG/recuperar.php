<?php
session_start();
require_once 'config/config.php';
require_once 'config/db_pdo.php';
require_once 'config/mailer.php'; // Cargamos el mailer

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Por favor, introduce tu correo electrónico.";
    } else {
        $db = DB::open();
        
        // 1. Buscamos si el correo existe en la tabla Alumnos
        $sql = "SELECT id, nombre FROM Alumnos WHERE email = ?";
        $alumno = $db->query($sql, [$email]);

        if (!empty($alumno)) {
            $user = $alumno[0];
            
            // 2. Generamos un token secreto y seguro
            $token = bin2hex(random_bytes(32)); // Ej: a8f5b3...
            
            // 3. Calculamos la caducidad (1 hora desde ahora)
            $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // 4. Guardamos el token en la base de datos
            $sqlUpdate = "UPDATE Alumnos SET reset_token = ?, token_expire = ? WHERE id = ?";
            try {
                $db->query($sqlUpdate, [$token, $expire, $user['id']]);
                
                // 5. ¡Enviamos el correo con el enlace mágico!
                $enviado = enviarCorreoRecuperacion($email, $user['nombre'], $token);

                if ($enviado) {
                    $mensaje = "Te hemos enviado un correo con las instrucciones para recuperar tu contraseña.";
                } else {
                    $error = "Hubo un problema al enviar el correo. Inténtalo de nuevo más tarde.";
                }
            } catch (Exception $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        } else {
            // TRUCO DE SEGURIDAD PROFESIONAL: 
            // Aunque el correo no exista, decimos que lo hemos enviado para que los hackers 
            // no puedan usar este formulario para adivinar qué correos están registrados.
            $mensaje = "Si el correo está registrado en nuestro sistema, recibirás un enlace de recuperación.";
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recuperar Contraseña — ACADEMIA TERRA</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
      .login-container { display: flex; justify-content: center; align-items: center; min-height: 60vh; padding: 20px; }
      .login-card { width: 100%; max-width: 450px; padding: 30px; }
      .error-msg { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
      .success-msg { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
      label { display: block; margin-bottom: 5px; font-weight: 500; text-align: center; }
      input { width: 100%; box-sizing: border-box; margin-bottom: 20px; text-align: center; }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container login-container">
    <section class="card login-card">
      <div style="text-align:center; margin-bottom:20px;">
        <h2 style="color: #3b82f6;">Recuperar Contraseña</h2>
        <p class="muted">Introduce tu correo y te enviaremos un enlace para crear una nueva contraseña.</p>
      </div>

      <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if(!empty($mensaje)): ?>
        <div class="success-msg"><?php echo $mensaje; ?></div>
      <?php else: ?>
          <form action="" method="POST">
            <label>Correo Electrónico</label>
            <input type="email" name="email" required placeholder="tu-correo@ejemplo.com">

            <button type="submit" class="btn" style="width:100%;">Enviar enlace de recuperación</button>
            
            <div style="text-align: center; margin-top: 20px; font-size: 0.9em;">
                <a href="login.php" style="color: #64748b; text-decoration: none;">Volver al inicio de sesión</a>
            </div>
          </form>
      <?php endif; ?>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>