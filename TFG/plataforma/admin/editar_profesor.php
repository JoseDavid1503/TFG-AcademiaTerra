<?php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../config/db_pdo.php';
$db = DB::open();

$id = (int)($_GET['id'] ?? 0);
$profesor = $db->query("SELECT * FROM Profesores WHERE id = ?", [$id]);

if (empty($profesor)) {
    exit("Profesor no encontrado.");
}
$p = $profesor[0];

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $dni = $_POST['dni'];
    $telefono = $_POST['telefono'];
    $curso = $_POST['curso_asignado'];

    $db->query("UPDATE Profesores SET nombre=?, apellidos=?, email=?, dni=?, telefono=?, curso_asignado=? WHERE id=?", 
               [$nombre, $apellidos, $email, $dni, $telefono, $curso, $id]);
    
    header('Location: index.php?view=profesores&msg=updated');
    exit;
}

$cursos = ['DAW_1', 'DAW_2', 'ASIR_1', 'ASIR_2', 'SMR_1', 'SMR_2'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Profesor - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css?v=<?php echo time(); ?>" />
</head>
<body class="bg-zinc-950 text-zinc-200">
    <?php include '../../includes/header.php'; ?>
    
    <div class="max-w-3xl mx-auto py-12 px-6">
        <a href="index.php?view=profesores" class="text-cyan-500 no-underline mb-6 inline-block">
            <i class="fa-solid fa-arrow-left mr-2"></i> Volver al panel
        </a>
        
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-white mb-6">Editar Perfil del Profesor</h2>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-zinc-400 text-sm mb-2">Nombre</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3 outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-zinc-400 text-sm mb-2">Apellidos</label>
                        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($p['apellidos']); ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3 outline-none focus:border-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-zinc-400 text-sm mb-2">DNI</label>
                        <input type="text" name="dni" value="<?php echo htmlspecialchars($p['dni']); ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3 outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-zinc-400 text-sm mb-2">Teléfono</label>
                        <input type="text" name="telefono" value="<?php echo htmlspecialchars($p['telefono']); ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3 outline-none focus:border-cyan-500">
                    </div>
                </div>

                <div>
                    <label class="block text-zinc-400 text-sm mb-2">Correo Electrónico</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($p['email']); ?>" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3 outline-none focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-zinc-400 text-sm mb-2">Curso Asignado</label>
                    <select name="curso_asignado" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl p-3 outline-none focus:border-cyan-500">
                        <?php foreach($cursos as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo ($p['curso_asignado'] == $c) ? 'selected' : ''; ?>>
                                <?php echo str_replace('_', ' ', $c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold py-4 rounded-xl transition-all shadow-lg shadow-cyan-500/20">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</body>
</html>