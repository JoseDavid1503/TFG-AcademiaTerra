<?php
session_start();
require_once 'config/config.php';
require_once 'config/db_pdo.php';

$error = '';
$mensaje = '';
$token_valido = false;
$id_alumno = null;

// 1. COMPROBAR EL TOKEN
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $db = DB::open();

    // Buscamos un alumno que tenga este token y que no haya caducado
    $sql = "SELECT id FROM Alumnos WHERE reset_token = ? AND token_expire > NOW()";
    $alumno = $db->query($sql, [$token]);

    if (!empty($alumno)) {
        $token_valido = true;
        $id_alumno = $alumno[0]['id'];
    } else {
        $error = "El enlace de recuperación no es válido o ha caducado. Vuelve a solicitar otro.";
    }
} else {
    $error = "No se ha proporcionado ningún token de recuperación.";
}

// 2. PROCESAR EL FORMULARIO (Cuando le dan a Guardar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];

    if (empty($password_1) || empty($password_2)) {
        $error = "Por favor, rellena ambos campos.";
    } elseif ($password_1 !== $password_2) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Encriptamos la nueva contraseña
        $passHash = password_hash($password_1, PASSWORD_DEFAULT);

        // Actualizamos la clave y borramos el token
        $sqlUpdate = "UPDATE Alumnos SET password = ?, reset_token = NULL, token_expire = NULL WHERE id = ?";
        
        try {
            $db->query($sqlUpdate, [$passHash, $id_alumno]);
            
            // Redirigimos al login con el mensaje de éxito que ya preparamos
            header('Location: login.php?pass_cambiada=1');
            exit;
        } catch (Exception $e) {
            $error = "Hubo un problema al actualizar tu contraseña: " . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Nueva Contraseña — ACADEMIA TERRA</title>
  <link rel="stylesheet" href="assets/css/styles.css" />
  <style>
      .login-container { display: flex; justify-content: center; align-items: center; min-height: 60vh; padding: 20px; }
      .login-card { width: 100%; max-width: 450px; padding: 30px; }
      .error-msg { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
      label { display: block; margin-bottom: 5px; font-weight: 500; text-align: center; }
      input { width: 100%; box-sizing: border-box; margin-bottom: 20px; text-align: center; }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container login-container">
    <section class="card login-card">
      <div style="text-align:center; margin-bottom:20px;">
        <h2 style="color: #3b82f6;">Crea una Nueva Contraseña</h2>
      </div>

      <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="recuperar.php" class="btn">Volver a solicitar enlace</a>
        </div>
      <?php endif; ?>

      <?php if($token_valido && empty($error)): ?>
          <form action="" method="POST">
            <p class="muted" style="text-align:center; margin-bottom:20px;">Escribe tu nueva contraseña a continuación.</p>
            
            <label>Nueva Contraseña</label>
            <input type="password" name="password_1" required placeholder="••••••••">

            <label>Repite la Contraseña</label>
            <input type="password" name="password_2" required placeholder="••••••••">

            <button type="submit" class="btn" style="width:100%;">Guardar y Entrar</button>
          </form>
      <?php endif; ?>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>