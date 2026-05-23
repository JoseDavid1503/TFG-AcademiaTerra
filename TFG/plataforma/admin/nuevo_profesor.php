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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $dni = trim($_POST['dni']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $curso = trim($_POST['curso_asignado']);
    $password_raw = $_POST['password'];

    // 1. Validar en AMBAS tablas para evitar duplicados globales
    $existe_alumno = $db->query("SELECT id FROM Alumnos WHERE email = ? OR dni = ?", [$email, $dni]);
    $existe_profe = $db->query("SELECT id FROM Profesores WHERE email = ? OR dni = ?", [$email, $dni]);
    
    if (!empty($existe_alumno) || !empty($existe_profe)) {
        $error = "Atención: El email o el DNI ya están registrados en la plataforma (como alumno o profesor).";
    } else {
        // 2. Hash de la contraseña
        $password_hashed = password_hash($password_raw, PASSWORD_BCRYPT);
        
        // 3. Inserción
        $sql = "INSERT INTO Profesores (nombre, apellidos, dni, email, telefono, curso_asignado, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        try {
            $db->query($sql, [$nombre, $apellidos, $dni, $email, $telefono, $curso, $password_hashed]);
            $exito = "Profesor dado de alta correctamente en el sistema.";
        } catch (Exception $e) {
            $error = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Profesor - Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
        .form-input { width: 100%; background: #18181b; border: 1px solid #3f3f46; border-radius: 12px; padding: 12px 16px; color: white; outline: none; transition: all 0.2s; }
        .form-input:focus { border-color: #a855f7; box-shadow: 0 0 0 2px rgba(168, 85, 247, 0.2); }
        .form-label { display: block; font-size: 0.85rem; font-weight: 600; color: #a1a1aa; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen">

    <?php include '../../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto py-12 px-6">
        
        <a href="index.php?view=profesores" class="text-zinc-500 hover:text-purple-400 transition-colors inline-flex items-center gap-2 mb-6 no-underline">
            <i class="fa-solid fa-arrow-left"></i> Volver a la lista de profesores
        </a>

        <div class="bg-zinc-900/80 backdrop-blur-md border border-zinc-800 rounded-3xl p-8 md:p-10 shadow-2xl">
            <div class="flex items-center gap-4 mb-2">
                <div class="w-12 h-12 bg-purple-500/10 text-purple-400 rounded-2xl flex items-center justify-center text-xl border border-purple-500/20">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <h1 class="text-3xl font-bold text-white title-font m-0">Añadir Profesor</h1>
            </div>
            <p class="text-zinc-400 mb-8">Registra un nuevo docente y asígnale su curso correspondiente.</p>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($exito): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $exito; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" required class="form-input" placeholder="Nombre del docente">
                    </div>
                    <div>
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" required class="form-input" placeholder="Apellidos completos">
                    </div>
                    <div>
                        <label class="form-label">DNI / NIE</label>
                        <input type="text" name="dni" required class="form-input" placeholder="Ej: 12345678Z">
                    </div>
                    <div>
                        <label class="form-label">Teléfono de contacto</label>
                        <input type="text" name="telefono" required class="form-input" placeholder="Ej: 611223344">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Correo Electrónico Corporativo</label>
                        <input type="email" name="email" required class="form-input" placeholder="usuario@academiaterra.com">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Curso / Ciclo Asignado</label>
                        <select name="curso_asignado" required class="form-input cursor-pointer">
                            <option value="">Selecciona el curso que gestionará...</option>
                            <option value="DAW_1">1º Desarrollo de Aplicaciones Web (DAW)</option>
                            <option value="DAW_2">2º Desarrollo de Aplicaciones Web (DAW)</option>
                            <option value="ASIR_1">1º Administración de Sistemas (ASIR)</option>
                            <option value="ASIR_2">2º Administración de Sistemas (ASIR)</option>
                            <option value="SMR_1">1º Sistemas Microinformáticos (SMR)</option>
                            <option value="SMR_2">2º Sistemas Microinformáticos (SMR)</option>
                        </select>
                    </div>
                </div>

                <div class="bg-purple-500/5 border border-purple-500/10 rounded-2xl p-6 mb-8">
                    <label class="form-label text-purple-400">Contraseña Provisional</label>
                    <input type="password" name="password" required class="form-input" placeholder="Mínimo 8 caracteres">
                    <p class="text-xs text-zinc-500 mt-3 flex items-center gap-2">
                        <i class="fa-solid fa-info-circle text-purple-400"></i>
                        Se recomienda una contraseña fuerte. El profesor podrá cambiarla tras su primer inicio de sesión.
                    </p>
                </div>

                <div class="flex justify-end items-center gap-4">
                    <a href="index.php?view=profesores" class="text-zinc-500 hover:text-white transition-colors font-medium no-underline">Cancelar</a>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white font-bold px-10 py-3.5 rounded-2xl transition-all shadow-lg shadow-purple-600/20 flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Registrar Profesor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>

</body>
</html>