<?php
require_once 'config/config.php';
require_once 'config/db_pdo.php';

// 1. Incluimos nuestro archivo de correos
require_once 'config/mailer.php'; 

$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recogemos y limpiamos los datos
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $dni = trim($_POST['dni']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Por favor, rellena todos los campos obligatorios.";
    } else {
        $db = DB::open();
        
        // 1. Comprobar si el email o DNI ya existen en ALUMNOS o PROFESORES
        // Nota: (dni != '' AND dni = ?) evita que dos usuarios sin DNI den un falso positivo
        $sqlCheckAlumnos = "SELECT id FROM Alumnos WHERE email = ? OR (dni != '' AND dni = ?)";
        $existe_alumno = $db->query($sqlCheckAlumnos, [$email, $dni]);

        $sqlCheckProfes = "SELECT id FROM Profesores WHERE email = ? OR (dni != '' AND dni = ?)";
        $existe_profe = $db->query($sqlCheckProfes, [$email, $dni]);

        if (!empty($existe_alumno) || !empty($existe_profe)) {
            $error = "Ese correo electrónico o DNI ya está registrado en la plataforma.";
        } else {
            // 2. Encriptar contraseña
            $passHash = password_hash($password, PASSWORD_DEFAULT);

            // 3. Insertar en la base de datos
            $sqlInsert = "INSERT INTO Alumnos (nombre, apellidos, dni, telefono, email, password) VALUES (?, ?, ?, ?, ?, ?)";
            
            try {
                // Guardamos en la base de datos
                $db->query($sqlInsert, [$nombre, $apellidos, $dni, $telefono, $email, $passHash]);
                
                // 4. ¡Disparamos el correo de bienvenida! 🚀
                enviarCorreoBienvenida($email, $nombre);

                // Redirigir al login con mensaje de éxito
                header('Location: login.php?registrado=1');
                exit;
            } catch (Exception $e) {
                $error = "Error al registrar el usuario: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registro — ACADEMIA TERRA</title>
  
  <link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>" />
  
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <main class="container login-container">
    <section class="card login-card" style="max-width: 600px;">
      <div style="text-align:center; margin-bottom:30px;">
        <h2 style="font-size: 2.2rem; margin-bottom: 10px;">Crear Cuenta</h2>
        <p class="muted" style="font-size: 1.05rem;">Rellena el formulario para acceder a tu aula virtual.</p>
      </div>

      <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="" method="POST">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0 20px;">
            <div>
                <label>Nombre *</label>
                <input type="text" name="nombre" required placeholder="Tu nombre">
            </div>
            <div>
                <label>Apellidos</label>
                <input type="text" name="apellidos" placeholder="Tus apellidos">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0 20px;">
            <div>
                <label>DNI</label>
                <input type="text" name="dni" placeholder="12345678X">
            </div>
            <div>
                <label>Teléfono</label>
                <input type="tel" name="telefono" placeholder="600...">
            </div>
        </div>

        <label>Email *</label>
        <input type="email" name="email" required placeholder="alumno@ejemplo.com">

        <label>Contraseña *</label>
        <input type="password" name="password" required placeholder="••••••••">

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:15px; font-size: 1.1rem; padding: 14px;">Registrarme</button>
        
        <div style="text-align: center; margin-top: 25px; font-size: 0.95em; color: var(--text-muted);">
            ¿Ya tienes cuenta? <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: 0.3s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">Inicia sesión aquí</a>
        </div>
      </form>
    </section>
  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>