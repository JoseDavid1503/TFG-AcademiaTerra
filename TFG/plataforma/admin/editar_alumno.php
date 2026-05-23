<?php
session_start();

// --- 🛡️ GUARDIA DE SEGURIDAD ---
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../config/db_pdo.php';

$db = DB::open();
$error = '';
$exito = '';

// 1. CARGAMOS LOS DATOS DEL ALUMNO
if (!isset($_GET['id'])) {
    // Si entran sin ID, los devolvemos a la tabla
    header('Location: index.php');
    exit;
}

$id_alumno = $_GET['id'];
/** @var array $alumno_db */
$alumno_db = $db->query("SELECT * FROM Alumnos WHERE id = ?", [$id_alumno]);

if (!is_array($alumno_db) || empty($alumno_db)) {
    // Si el ID no existe en la BD, los devolvemos a la tabla
    header('Location: index.php');
    exit;
}
$alumno = $alumno_db[0]; // Guardamos los datos del alumno

// 2. GUARDAMOS LOS CAMBIOS SI SE PULSA EL BOTÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $curso_matriculado = $_POST['curso_matriculado']; // Obtenemos el nuevo campo de curso
    $nueva_password = $_POST['password']; // Es opcional

    try {
        if (!empty($nueva_password)) {
            // Si el admin escribió una clave nueva, la encriptamos y la guardamos (junto al curso)
            $pass_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            $sql = "UPDATE Alumnos SET nombre = ?, apellidos = ?, dni = ?, email = ?, telefono = ?, curso_matriculado = ?, password = ? WHERE id = ?";
            $db->query($sql, [$nombre, $apellidos, $dni, $email, $telefono, $curso_matriculado, $pass_hash, $id_alumno]);
        } else {
            // Si la dejó en blanco, no tocamos la contraseña y actualizamos lo demás (incluido el curso)
            $sql = "UPDATE Alumnos SET nombre = ?, apellidos = ?, dni = ?, email = ?, telefono = ?, curso_matriculado = ? WHERE id = ?";
            $db->query($sql, [$nombre, $apellidos, $dni, $email, $telefono, $curso_matriculado, $id_alumno]);
        }

        $exito = "¡Los datos del alumno se han actualizado correctamente!";
        
        // Recargamos los datos para que el formulario ya muestre los nuevos
        /** @var array $nuevo_alumno_db */
        $nuevo_alumno_db = $db->query("SELECT * FROM Alumnos WHERE id = ?", [$id_alumno]);
        $alumno = $nuevo_alumno_db[0];
        
    } catch (Exception $e) {
        $error = "Error al actualizar los datos. Comprueba que el email o DNI no estén repetidos.";
    }
}

// Obtenemos el curso actual del alumno (o "Sin matricular" por defecto)
$curso_actual = $alumno['curso_matriculado'] ?? 'Sin matricular';

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Alumno — ACADEMIA TERRA</title>
  <link rel="stylesheet" href="../../assets/css/styles.css?v=<?php echo time(); ?>" />
  <style>
      .form-container { max-width: 700px; margin: 0 auto; background: var(--glass); backdrop-filter: blur(10px); padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid var(--border-light); }
      .form-group { margin-bottom: 20px; }
      .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #e0e7ff; }
      .form-group input, .form-group select { width: 100%; box-sizing: border-box; padding: 12px 15px; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; background: rgba(10, 15, 28, 0.6); color: white; font-size: 1rem; outline: none; transition: 0.3s; }
      .form-group input:focus, .form-group select:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(0, 212, 255, 0.2); }
      .success-msg { color: #10b981; background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
      .error-msg { color: #f87171; background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
  </style>
</head>
<body>
  
  <?php include '../../includes/header.php'; ?>

  <main class="container" style="padding-top: 40px; padding-bottom: 60px;">
    
    <div style="margin-bottom: 25px; max-width: 700px; margin-left: auto; margin-right: auto;">
        <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: 0.3s; display: inline-flex; align-items: center;">
            <span style="margin-right: 5px;">←</span> Volver al listado
        </a>
    </div>

    <section class="form-container">
        <h2 style="color: var(--primary); text-align: center; margin-bottom: 10px; font-size: 2.2rem;">Editar Alumno</h2>
        <p class="muted" style="text-align: center; margin-bottom: 30px; font-size: 1.1rem;">Modificando a: <b style="color: white;"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']); ?></b></p>

        <?php if(!empty($exito)): ?>
            <div class="success-msg"><?php echo $exito; ?></div>
        <?php endif; ?>

        <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($alumno['nombre']); ?>" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label>Apellidos</label>
                    <input type="text" name="apellidos" value="<?php echo htmlspecialchars($alumno['apellidos']); ?>" required>
                </div>
            </div>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label>DNI</label>
                    <input type="text" name="dni" value="<?php echo htmlspecialchars($alumno['dni']); ?>" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($alumno['telefono']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Curso Matriculado</label>
                <select name="curso_matriculado" required>
                    <option value="Sin matricular" <?php if($curso_actual == 'Sin matricular') echo 'selected'; ?>>❌ Sin matricular</option>
                    <option value="DAW_1" <?php if($curso_actual == 'DAW_1') echo 'selected'; ?>>📘 1º DAW (Desarrollo de Aplicaciones Web)</option>
                    <option value="DAW_2" <?php if($curso_actual == 'DAW_2') echo 'selected'; ?>>📘 2º DAW (Desarrollo de Aplicaciones Web)</option>
                    <option value="ASIR_1" <?php if($curso_actual == 'ASIR_1') echo 'selected'; ?>>📙 1º ASIR (Admin. Sistemas Informáticos en Red)</option>
                    <option value="ASIR_2" <?php if($curso_actual == 'ASIR_2') echo 'selected'; ?>>📙 2º ASIR (Admin. Sistemas Informáticos en Red)</option>
                    <option value="SMR_1" <?php if($curso_actual == 'SMR_1') echo 'selected'; ?>>📗 1º SMR (Sistemas Microinformáticos y Redes)</option>
                    <option value="SMR_2" <?php if($curso_actual == 'SMR_2') echo 'selected'; ?>>📗 2º SMR (Sistemas Microinformáticos y Redes)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($alumno['email']); ?>" required>
            </div>

            <div class="form-group" style="margin-top: 35px; padding-top: 25px; border-top: 1px solid rgba(255,255,255,0.1);">
                <label style="color: #f87171;">Cambiar Contraseña (Opcional)</label>
                <input type="text" name="password" placeholder="Escribe aquí solo si quieres cambiarla">
                <small class="muted" style="display: block; margin-top: 8px;">Si dejas este campo en blanco, la contraseña seguirá siendo la misma.</small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 25px; padding: 15px; font-size: 1.1rem;">Guardar Cambios</button>
        </form>
    </section>

  </main>

  <?php include '../../includes/footer.php'; ?>

</body>
</html>