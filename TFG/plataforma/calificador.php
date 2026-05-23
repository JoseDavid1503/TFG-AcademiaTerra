<?php
session_start();

if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'profesor') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../config/db_pdo.php';
$db = DB::open();

$nombre_asignatura = $_GET['asignatura'] ?? '';
if (empty($nombre_asignatura)) {
    exit("Asignatura no especificada.");
}

$nombres_unidades = [
    'general' => 'General',
    '1' => 'Unidad 1',
    '2' => 'Unidad 2',
    '3' => 'Unidad 3',
    '4' => 'Unidad 4',
    '5' => 'Unidad 5',
    'examenes' => 'Exámenes',
    'tutoria' => 'Tutoría',
    'practicas' => 'Prácticas voluntarias'
];

// ==========================================
// ⚙️ LÓGICA DE FILTROS Y PAGINACIÓN
// ==========================================

$limit_options = [5, 10, 15, 20];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$filtro_unidad = $_GET['unidad'] ?? 'todas';
$busqueda = trim($_GET['search'] ?? '');

$params = [$nombre_asignatura];
$where_sql = "r.asignatura = ? AND r.tipo IN ('tarea', 'tarea_opcional')";

if ($filtro_unidad !== 'todas') {
    $where_sql .= " AND r.unidad = ?";
    $params[] = $filtro_unidad;
}

if ($busqueda !== '') {
    $where_sql .= " AND r.titulo LIKE ?";
    $params[] = "%$busqueda%";
}

// 1. Obtener Estadísticas Globales (Independientes de los filtros, para las tarjetas de arriba)
$stats_totales = (int)$db->query("SELECT COUNT(id) as c FROM Recursos WHERE asignatura = ? AND tipo IN ('tarea', 'tarea_opcional')", [$nombre_asignatura])[0]['c'];
$stats_pendientes = (int)$db->query("SELECT COUNT(e.id) as c FROM Entregas e JOIN Recursos r ON e.recurso_id = r.id WHERE r.asignatura = ? AND (e.nota IS NULL OR e.nota = '') AND e.archivo_url IS NOT NULL", [$nombre_asignatura])[0]['c'];

// 2. Contar resultados para la paginación
$count_sql = "SELECT COUNT(DISTINCT r.id) as total FROM Recursos r WHERE $where_sql";
$total_res = $db->query($count_sql, $params);
$total_rows = (int)($total_res[0]['total'] ?? 0);
$total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

// 3. Consulta principal paginada
$sql = "
    SELECT r.id, r.titulo, r.unidad, r.fecha_limite, r.fecha_creacion,
           COUNT(e.id) as total_entregas,
           SUM(CASE WHEN (e.nota IS NULL OR e.nota = '') AND e.archivo_url IS NOT NULL THEN 1 ELSE 0 END) as pendientes_corregir
    FROM Recursos r
    LEFT JOIN Entregas e ON r.id = e.recurso_id
    WHERE $where_sql
    GROUP BY r.id, r.titulo, r.unidad, r.fecha_limite, r.fecha_creacion
    ORDER BY r.fecha_creacion DESC
    LIMIT $limit OFFSET $offset
";
$tareas = $db->query($sql, $params);
if (!$tareas) $tareas = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificador - <?php echo htmlspecialchars($nombre_asignatura); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <div class="flex-grow max-w-7xl mx-auto w-full py-10 px-6">
        <a href="aula.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>" class="text-cyan-500 hover:text-cyan-400 no-underline mb-6 inline-block transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Volver al aula
        </a>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10 border-b border-zinc-800 pb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-amber-500/10 flex justify-center items-center text-3xl border border-amber-500/30 text-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white m-0 title-font">Centro de Calificaciones</h1>
                    <p class="text-zinc-400 m-0"><?php echo htmlspecialchars($nombre_asignatura); ?></p>
                </div>
            </div>
            
            <div class="flex gap-4">
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl px-5 py-3 text-center">
                    <div class="text-2xl font-bold text-white"><?php echo $stats_totales; ?></div>
                    <div class="text-xs text-zinc-500 uppercase font-semibold">Tareas Totales</div>
                </div>
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl px-5 py-3 text-center">
                    <div class="text-2xl font-bold text-amber-500"><?php echo $stats_pendientes; ?></div>
                    <div class="text-xs text-amber-500/70 uppercase font-semibold">Por Corregir</div>
                </div>
            </div>
        </div>

        <form method="GET" class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4 mb-6 shadow-lg flex flex-wrap gap-4 items-end">
            <input type="hidden" name="asignatura" value="<?php echo htmlspecialchars($nombre_asignatura); ?>">
            
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-zinc-400 mb-2 uppercase tracking-wider font-semibold">Buscar por Título</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Ej: Práctica 1..." class="w-full bg-zinc-800 border border-zinc-700 text-white pl-11 pr-4 py-2.5 rounded-xl outline-none focus:border-cyan-500 transition-colors">
                </div>
            </div>

            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs text-zinc-400 mb-2 uppercase tracking-wider font-semibold">Unidad / Tema</label>
                <select name="unidad" class="w-full bg-zinc-800 border border-zinc-700 text-white p-2.5 rounded-xl outline-none focus:border-cyan-500 transition-colors">
                    <option value="todas">Todas las unidades</option>
                    <?php foreach($nombres_unidades as $key => $nombre): ?>
                        <option value="<?php echo $key; ?>" <?php echo $filtro_unidad === (string)$key ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex-none w-[120px]">
                <label class="block text-xs text-zinc-400 mb-2 uppercase tracking-wider font-semibold">Mostrar</label>
                <select name="limit" class="w-full bg-zinc-800 border border-zinc-700 text-white p-2.5 rounded-xl outline-none focus:border-cyan-500 transition-colors">
                    <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                    <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                </select>
            </div>

            <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold px-6 py-2.5 rounded-xl transition-colors border-none cursor-pointer h-[46px]">
                <i class="fa-solid fa-filter mr-1"></i> Filtrar
            </button>
            
            <?php if($busqueda !== '' || $filtro_unidad !== 'todas'): ?>
                <a href="calificador.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>" class="bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold px-4 py-2.5 rounded-xl transition-colors border border-zinc-700 cursor-pointer h-[46px] flex items-center no-underline">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </form>

        <div class="bg-zinc-900 border border-zinc-800 rounded-t-2xl shadow-xl overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-zinc-800/50 border-b border-zinc-700">
                        <th class="p-5 text-zinc-400 font-medium">Tarea</th>
                        <th class="p-5 text-zinc-400 font-medium">Ubicación</th>
                        <th class="p-5 text-zinc-400 font-medium text-center">Entregas Totales</th>
                        <th class="p-5 text-zinc-400 font-medium text-center">Pendientes de Corregir</th>
                        <th class="p-5 text-zinc-400 font-medium text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php if(empty($tareas)): ?>
                        <tr><td colspan="5" class="p-10 text-center text-zinc-500">No hay tareas que coincidan con tu búsqueda.</td></tr>
                    <?php else: ?>
                        <?php foreach($tareas as $t): ?>
                        <tr class="hover:bg-zinc-800/30 transition-colors">
                            <td class="p-5">
                                <div class="font-bold text-white text-lg flex items-center gap-2">
                                    <i class="fa-solid fa-file-arrow-up text-blue-400 text-sm"></i>
                                    <?php echo htmlspecialchars($t['titulo']); ?>
                                </div>
                                <?php if($t['fecha_limite']): ?>
                                    <div class="text-xs text-zinc-500 mt-1"><i class="fa-regular fa-calendar-xmark mr-1"></i> Límite: <?php echo date('d/m/Y', strtotime($t['fecha_limite'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="p-5 text-zinc-400">
                                <span class="bg-zinc-800 px-3 py-1 rounded-lg text-sm border border-zinc-700">
                                    <?php echo $nombres_unidades[$t['unidad']] ?? 'Desconocida'; ?>
                                </span>
                            </td>
                            <td class="p-5 text-center text-zinc-300 font-medium">
                                <?php echo $t['total_entregas']; ?>
                            </td>
                            <td class="p-5 text-center">
                                <?php if($t['pendientes_corregir'] > 0): ?>
                                    <span class="bg-amber-500 text-zinc-900 px-3 py-1 rounded-full text-sm font-bold shadow-[0_0_10px_rgba(245,158,11,0.4)]">
                                        <?php echo $t['pendientes_corregir']; ?> pendientes
                                    </span>
                                <?php else: ?>
                                    <span class="text-emerald-500/50 text-sm font-medium"><i class="fa-solid fa-check mr-1"></i>Al día</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-5 text-right">
                                <a href="tarea.php?id=<?php echo $t['id']; ?>" class="inline-block bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold px-5 py-2 rounded-xl transition-colors no-underline shadow-lg shadow-cyan-500/20">
                                    Ir a corregir <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="bg-zinc-900 border border-zinc-800 border-t-0 rounded-b-2xl p-4 flex items-center justify-between shadow-xl">
            <span class="text-zinc-400 text-sm font-medium">Mostrando página <?php echo $page; ?> de <?php echo $total_pages; ?></span>
            <div class="flex gap-2">
                <?php 
                    $q_params = $_GET;
                    if ($page > 1) {
                        $q_params['page'] = $page - 1;
                        echo '<a href="?'.http_build_query($q_params).'" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-sm transition-colors no-underline font-semibold border border-zinc-700"><i class="fa-solid fa-chevron-left mr-1"></i> Anterior</a>';
                    }
                    if ($page < $total_pages) {
                        $q_params['page'] = $page + 1;
                        echo '<a href="?'.http_build_query($q_params).'" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-sm transition-colors no-underline font-semibold border border-zinc-700">Siguiente <i class="fa-solid fa-chevron-right ml-1"></i></a>';
                    }
                ?>
            </div>
        </div>
        <?php else: ?>
            <div class="bg-zinc-900 border border-zinc-800 border-t-0 rounded-b-2xl h-4 shadow-xl"></div>
        <?php endif; ?>

    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>