<?php
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'alumno') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../config/db_pdo.php';
$db = DB::open();

$id_alumno = $_SESSION['user_id'];

// 1. Obtener datos del alumno
$alumno = $db->query("SELECT * FROM Alumnos WHERE id = ?", [$id_alumno]);
if (empty($alumno)) {
    exit("Error: Alumno no encontrado.");
}
$curso_actual = $alumno[0]['curso_matriculado'];

// 2. Lógica de Enrutamiento (Onboarding vs Aula Virtual)
$cursos_disponibles = []; // Inicializamos para evitar el aviso de Intelephense
$asignaturas = [];        // Inicializamos para evitar el aviso de Intelephense

if (empty($curso_actual)) {
    $modo_onboarding = true;
    // Si no tiene curso, sacamos el catálogo de cursos activos
    $cursos_disponibles = $db->query("SELECT * FROM Cursos WHERE activo = 1");
    if (!$cursos_disponibles) $cursos_disponibles = [];
} else {
    $modo_onboarding = false;
    // Si tiene curso, sacamos sus asignaturas
    $asignaturas = $db->query("SELECT * FROM Asignaturas WHERE curso = ?", [$curso_actual]);
    if (!$asignaturas) $asignaturas = [];
}

// 3. Diccionario de mapeo para mostrar los nombres académicos en la interfaz
$formato_nombres = [
    'ASIR1' => '1º ASIR',
    'ASIR'  => '2º ASIR',
    'DAW1'  => '1º DAW',
    'DAW2'  => '2º DAW',
    'SMR1'  => '1º SMR',
    'SMR2'  => '2º SMR'
];

// Conversión del nombre del curso para el encabezado superior
$curso_mostrar = 'Sin asignar';
if (!empty($curso_actual)) {
    $curso_mostrar = $formato_nombres[$curso_actual] ?? $curso_actual;
}

// Array de iconos para las asignaturas
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
    <title>Mi Panel - Academia Terra</title>
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
        
        <p class="text-zinc-400 mb-10">Curso actual: <span class="text-cyan-400 font-semibold"><?php echo htmlspecialchars($curso_mostrar); ?></span></p>

        <?php if ($modo_onboarding): ?>
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-10 text-center shadow-2xl">
                <div class="w-24 h-24 bg-cyan-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-cyan-500/20">
                    <i class="fa-solid fa-rocket text-5xl text-cyan-400"></i>
                </div>
                <h2 class="text-3xl font-bold text-white mb-4">¡Bienvenido a Academia Terra!</h2>
                <p class="text-zinc-400 mb-10 max-w-2xl mx-auto text-lg">Actualmente no estás matriculado en ningún curso. Explora nuestra oferta formativa y comienza tu camino hacia el éxito.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-left">
                    <?php foreach($cursos_disponibles as $curso_disp): ?>
                        <?php 
                            $nombre_db = $curso_disp['nombre'];
                            $nombre_pantalla = $formato_nombres[$nombre_db] ?? $nombre_db;
                        ?>
                        <div class="bg-zinc-950 border border-zinc-800 rounded-xl p-6 card-hover flex flex-col">
                            <h3 class="text-xl font-bold text-white mb-3"><?php echo htmlspecialchars($nombre_pantalla); ?></h3>
                            <p class="text-sm text-zinc-400 mb-6 flex-grow"><?php echo htmlspecialchars($curso_disp['descripcion'] ?? 'Descripción no disponible'); ?></p>
                            
                            <form action="procesar_onboarding.php" method="POST">
                                <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($curso_disp['id']); ?>">
                                <input type="hidden" name="nombre_curso" value="<?php echo htmlspecialchars($nombre_db); ?>">
                                <button type="submit" class="w-full bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold py-3 px-4 rounded-xl transition-colors shadow-[0_0_15px_rgba(0,212,255,0.3)]">
                                    <i class="fa-solid fa-plus mr-2"></i> Matricularme
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($cursos_disponibles)): ?>
                       <div class="col-span-full p-6 text-center border border-zinc-800 rounded-xl">
                           <p class="text-zinc-500"><i class="fa-solid fa-triangle-exclamation mr-2"></i> No hay cursos disponibles en este momento.</p>
                       </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <?php if (empty($asignaturas)): ?>
                    <div class="col-span-full p-10 text-center border border-zinc-800 rounded-2xl bg-zinc-900/50 mt-4">
                        <i class="fa-solid fa-folder-open text-5xl text-zinc-700 mb-4 block"></i>
                        <h3 class="text-xl font-bold text-white mb-2">Aún no hay asignaturas disponibles</h3>
                        <p class="text-zinc-500">No se han encontrado módulos formativos asociados a tu curso actual. Si crees que esto es un error, por favor contacta con secretaría.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($asignaturas as $asig): ?>
                        <?php
                            $nombre_asig = $asig['nombre'];
                            $icono_mostrar = $iconos[$nombre_asig] ?? 'fa-solid fa-book';

                            // 1. Tareas totales visibles
                            $res_totales = $db->query("SELECT COUNT(*) as total FROM Recursos WHERE asignatura = ? AND tipo IN ('tarea', 'tarea_opcional') AND visible = 1", [$nombre_asig]);
                            $total_tareas = (int)($res_totales[0]['total'] ?? 0);

                            // 2. Tareas entregadas
                            $res_entregadas = $db->query("
                                SELECT COUNT(DISTINCT e.recurso_id) as completadas 
                                FROM Entregas e 
                                JOIN Recursos r ON e.recurso_id = r.id 
                                WHERE r.asignatura = ? AND e.alumno_id = ? AND r.tipo IN ('tarea', 'tarea_opcional') AND r.visible = 1
                            ", [$nombre_asig, $id_alumno]);
                            $tareas_completadas = (int)($res_entregadas[0]['completadas'] ?? 0);

                            // 3. Tareas pendientes
                            $tareas_pendientes = $total_tareas - $tareas_completadas;

                            // 4. Calcular porcentaje
                            $porcentaje = 0;
                            if ($total_tareas > 0) {
                                $porcentaje = round(($tareas_completadas / $total_tareas) * 100);
                            }
                            
                            // 5. Estilos basados en el progreso
                            $color_barra = 'bg-cyan-500';
                            $color_texto = 'text-zinc-400';
                            if ($porcentaje == 100 && $total_tareas > 0) {
                                $color_barra = 'bg-emerald-500'; 
                                $color_texto = 'text-emerald-400 font-bold';
                            } elseif ($porcentaje > 0) {
                                $color_texto = 'text-cyan-400 font-medium'; 
                            }
                        ?>
                        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 card-hover flex flex-col h-full relative overflow-hidden">
                            
                            <div class="flex justify-between items-start mb-6">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex justify-center items-center text-cyan-400 text-xl border border-cyan-500/20">
                                        <i class="<?php echo $icono_mostrar; ?>"></i>
                                    </div>
                                    <?php if ($tareas_pendientes > 0): ?>
                                        <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-zinc-900 animate-pulse-soft shadow-[0_0_10px_rgba(239,68,68,0.5)]" title="<?php echo $tareas_pendientes; ?> tarea(s) pendiente(s)">
                                            <?php echo $tareas_pendientes; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm <?php echo $color_texto; ?>"><?php echo $porcentaje; ?>%</span>
                            </div>
                            
                            <h3 class="text-xl font-bold text-white mb-6 flex-grow"><?php echo htmlspecialchars($nombre_asig); ?></h3>
                            
                            <div class="w-full bg-zinc-800 rounded-full h-1.5 mb-6 overflow-hidden shadow-inner">
                                <div class="<?php echo $color_barra; ?> h-1.5 rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(0,212,255,0.5)]" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>

                            <a href="../aula.php?asignatura=<?php echo urlencode($nombre_asig); ?>&unidad=general" class="w-full block text-center border border-cyan-500/50 text-cyan-400 hover:bg-cyan-500 hover:text-zinc-900 font-semibold py-2.5 rounded-xl transition-colors no-underline">
                                Entrar al aula
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>