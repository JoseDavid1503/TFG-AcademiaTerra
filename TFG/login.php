<?php
session_start();
require_once 'config/config.php';
require_once 'config/db_pdo.php';

// --- LOGIN INTELIGENTE ---
if (!empty($_SESSION['user_id'])) {
    if ($_SESSION['tipo_usuario'] === 'admin') {
        header('Location: plataforma/admin/index.php');
    } elseif ($_SESSION['tipo_usuario'] === 'profesor') {
        header('Location: plataforma/profesor/index.php');
    } else {
        header('Location: plataforma/alumno/index.php');
    }
    exit;
}

require_once 'config/google_config.php'; 

$error = '';

if (isset($_SESSION['error_login'])) {
    $error = $_SESSION['error_login'];
    unset($_SESSION['error_login']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['usuario']); 
    $password = $_POST['password'];
    $db = DB::open();

    if ($db) {
        // 1. ADMIN
        $sqlAdmin = "SELECT * FROM Administradores WHERE email = ?";
        $resAdmin = $db->query($sqlAdmin, [$email]);
        if (!empty($resAdmin)) {
            $user = $resAdmin[0];
            if ($password === $user['password'] || password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol']; 
                $_SESSION['foto'] = $user['foto'] ?? '';
                $_SESSION['tipo_usuario'] = 'admin';
                header('Location: plataforma/admin/index.php');
                exit;
            }
        }

        // 2. PROFESOR
        if (empty($_SESSION['user_id'])) {
            $sqlProf = "SELECT * FROM Profesores WHERE email = ?";
            $resProf = $db->query($sqlProf, [$email]);
            if (!empty($resProf)) {
                $user = $resProf[0];
                if ($password === $user['password'] || password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nombre'] = $user['nombre'];
                    $_SESSION['foto'] = $user['foto'] ?? '';
                    $_SESSION['tipo_usuario'] = 'profesor';
                    header('Location: plataforma/profesor/index.php');
                    exit;
                }
            }
        }

        // 3. ALUMNO (ACTUALIZADO PARA PERSISTENCIA DE CURSO)
        if (empty($_SESSION['user_id'])) {
            $sqlAlu = "SELECT * FROM Alumnos WHERE email = ?";
            $resAlu = $db->query($sqlAlu, [$email]);
            if (!empty($resAlu)) {
                $user = $resAlu[0];
                if ($password === $user['password'] || password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nombre'] = $user['nombre'];
                    $_SESSION['email'] = $user['email']; // Guardamos email para el mailer
                    $_SESSION['dni'] = $user['dni'] ?? ''; 
                    $_SESSION['telefono'] = $user['telefono'] ?? '';
                    $_SESSION['foto'] = $user['foto'] ?? '';
                    $_SESSION['tipo_usuario'] = 'alumno';
                    
                    // --- CLAVE: Recuperamos el curso de la base de datos ---
                    $_SESSION['curso_matriculado'] = $user['curso_matriculado']; 
                    
                    header('Location: plataforma/alumno/index.php');
                    exit;
                }
            }
        }

        $error = "El usuario o la contraseña son incorrectos.";
    } else {
        $error = "Error de conexión con la base de datos.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Acceso Usuarios — ACADEMIA TERRA</title>
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container login-container">
    <section class="card login-card">
      <div style="text-align:center; margin-bottom:25px;">
        <img src="assets/img/acceso.png" alt="Acceso" class="login-img">
        <h2 style="margin-top: 10px;">Bienvenido de nuevo</h2>
        <p class="muted" style="font-size: 1rem;">Introduce tus credenciales para acceder a tu panel.</p>
      </div>

      <?php if(isset($_GET['registrado'])): ?>
        <div class="success-msg">¡Registro completado! Ahora puedes iniciar sesión.</div>
      <?php endif; ?>
      <?php if(isset($_GET['pass_cambiada'])): ?>
        <div class="success-msg">¡Contraseña actualizada! Ya puedes entrar con tu nueva clave.</div>
      <?php endif; ?>
      <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="" method="POST">
        <div style="margin-bottom: 10px;">
            <label>Correo Electrónico</label>
            <input type="email" name="usuario" placeholder="ejemplo@academia.com" required>
        </div>
        <div style="margin-bottom: 30px;">
            <label>Contraseña</label>
            <input type="password" name="password" placeholder="••••••••" required>
            <div style="text-align: right; margin-top: -10px;">
                <a href="recuperar.php" style="color: var(--primary); font-size: 0.9em; text-decoration: none; font-weight: 500; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">¿Has olvidado tu contraseña?</a>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width:100%; font-size: 1.1rem; padding: 14px;">Entrar a mi cuenta</button>

        <div style="margin-top: 25px; text-align: center;">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <span style="height: 1px; background: var(--border-light); flex: 1;"></span>
                <span style="padding: 0 15px; color: var(--text-muted); font-size: 0.9em; font-weight: 500;">O CONTINUAR CON</span>
                <span style="height: 1px; background: var(--border-light); flex: 1;"></span>
            </div>
            
            <a href="<?php echo $client->createAuthUrl(); ?>" style="display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.05); color: white; border: 1px solid var(--border-light); padding: 12px 20px; border-radius: 50px; text-decoration: none; font-weight: 500; transition: all 0.3s; width: 100%; box-sizing: border-box;" onmouseover="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.borderColor='var(--primary)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.05)'; this.style.borderColor='var(--border-light)'">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="G" style="width: 20px; margin-right: 12px;">
                Iniciar sesión con Google
            </a>
        </div>

        <div style="text-align: center; margin-top: 25px; font-size: 0.95em; color: var(--text-muted);">
            ¿No tienes cuenta? <a href="registro.php" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Regístrate gratis</a>
        </div>
      </form>
    </section>
  </main>
  
  <?php include 'includes/footer.php'; ?>
</body>
</html>