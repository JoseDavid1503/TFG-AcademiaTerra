<?php
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'profesor') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../config/db_pdo.php';
$db = DB::open();

$id_profesor = $_SESSION['user_id'];

// 1. Obtener datos del profesor
$profesor = $db->query("SELECT * FROM Profesores WHERE id = ?", [$id_profesor]);
if (empty($profesor)) {
    exit("Error: Profesor no encontrado.");
}
$curso_actual = $profesor[0]['curso_asignado'] ?? 'Sin curso asignado';

// 2. Obtener asignaturas de ese curso
$asignaturas = $db->query("SELECT * FROM Asignaturas WHERE curso = ?", [$curso_actual]);
if (!$asignaturas) $asignaturas = [];

// Array de iconos para que coincidan visualmente
$iconos = [
    'Sistemas Informáticos' => 'fa-solid fa-server',
    'Bases de Datos' => 'fa-solid fa-database',
    'Programación' => 'fa-solid fa-code',
    'Lenguajes de Marcas' => 'fa-solid fa-file-code',
    'Entornos de Desarrollo' => 'fa-solid fa-laptop-code',
    'Formación y Orientación Laboral' => 'fa-solid fa-briefcase',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Profesor - Academia Terra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 30px -10px rgba(0, 212, 255, 0.15); 
            border-color: rgba(0, 212, 255, 0.3); 
        }
        /* Animación para el badge de notificación */
        @keyframes pulse-soft {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .animate-pulse-soft {
            animation: pulse-soft 2s infinite;
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen flex flex-col">

    <?php include '../../includes/header.php'; ?>

    <div class="flex-grow max-w-7xl mx-auto w-full py-10 px-6">
        <h1 class="text-3xl font-bold title-font text-white mb-2">Mi Aula Virtual</h1>
        <p class="text-zinc-400 mb-1">Curso actual: <span class="text-cyan-400 font-semibold"><?php echo htmlspecialchars($curso_actual); ?></span></p>
        <p class="text-zinc-500 text-sm mb-10"><i class="fa-solid fa-user-tie mr-1"></i> Modo Profesor</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($asignaturas as $asig): ?>
                <?php
                    $nombre_asig = $asig['nombre'];
                    $icono_mostrar = $iconos[$nombre_asig] ?? 'fa-solid fa-book';

                    // ==========================================
                    // 📊 LÓGICA DE NOTIFICACIONES PARA EL PROFESOR
                    // ==========================================
                    
                    // Buscamos todas las entregas de esta asignatura donde el profesor NO haya puesto nota aún
                    $res_pendientes = $db->query("
                        SELECT COUNT(e.id) as pendientes 
                        FROM Entregas e 
                        JOIN Recursos r ON e.recurso_id = r.id 
                        WHERE r.asignatura = ? AND (e.nota IS NULL OR e.nota = '')
                    ", [$nombre_asig]);
                    
                    $entregas_pendientes = (int)($res_pendientes[0]['pendientes'] ?? 0);
                ?>
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 card-hover flex flex-col h-full relative overflow-hidden">
                    
                    <div class="flex justify-between items-start mb-6">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex justify-center items-center text-cyan-400 text-xl border border-cyan-500/20">
                                <i class="<?php echo $icono_mostrar; ?>"></i>
                            </div>
                            
                            <?php if ($entregas_pendientes > 0): ?>
                                <div class="absolute -top-2 -right-2 bg-amber-500 text-zinc-900 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-zinc-900 animate-pulse-soft shadow-[0_0_10px_rgba(245,158,11,0.5)]" title="Tienes <?php echo $entregas_pendientes; ?> entrega(s) por corregir">
                                    <?php echo $entregas_pendientes; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 bg-zinc-800 px-3 py-1 rounded-full border border-zinc-700">Gestión</span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-6 flex-grow"><?php echo htmlspecialchars($nombre_asig); ?></h3>
                    
                    <div class="w-full bg-zinc-800 rounded-full h-1.5 mb-6 overflow-hidden shadow-inner">
                        <div class="bg-cyan-500/20 h-1.5 rounded-full" style="width: 100%"></div>
                    </div>

                    <a href="../aula.php?asignatura=<?php echo urlencode($nombre_asig); ?>&unidad=general" class="w-full block text-center border border-cyan-500/50 text-cyan-400 hover:bg-cyan-500 hover:text-zinc-900 font-semibold py-2.5 rounded-xl transition-colors no-underline">
                        Entrar al aula
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>