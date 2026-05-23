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

// ==========================================
// 📊 CONSULTAS PARA LOS GRÁFICOS (NUEVO)
// ==========================================

// 1. Alumnos por Curso (Para el gráfico Doughnut)
$sqlAlumnosPorCurso = "SELECT curso_matriculado, COUNT(*) as total FROM Alumnos WHERE curso_matriculado IS NOT NULL AND curso_matriculado != '' GROUP BY curso_matriculado";
$resultAlumnosPorCurso = $db->query($sqlAlumnosPorCurso);
$cursosNombres = [];
$cursosTotales = [];
if (is_array($resultAlumnosPorCurso)) {
    foreach ($resultAlumnosPorCurso as $row) {
        $cursosNombres[] = str_replace('_', ' ', $row['curso_matriculado']);
        $cursosTotales[] = (int)$row['total'];
    }
}

// 2. Total Alumnos vs Profesores (Para el gráfico de Barras)
$totalAlumnosChart = 0;
$totalProfesoresChart = 0;
$resTotalAlumnos = $db->query("SELECT COUNT(id) as total FROM Alumnos");
if (!empty($resTotalAlumnos)) $totalAlumnosChart = (int)$resTotalAlumnos[0]['total'];

$resTotalProfesores = $db->query("SELECT COUNT(id) as total FROM Profesores");
if (!empty($resTotalProfesores)) $totalProfesoresChart = (int)$resTotalProfesores[0]['total'];

// ==========================================
// ⚙️ LÓGICA DE VISTA DE TABLAS
// ==========================================
$view = $_GET['view'] ?? 'alumnos';
if (!in_array($view, ['alumnos', 'profesores'])) {
    $view = 'alumnos';
}

if ($view === 'profesores') {
    $table = 'Profesores';
    $curso_field = 'curso_asignado';
    $edit_url = 'editar_profesor.php';
    $delete_url = 'borrar_profesor.php';
    $add_url = 'nuevo_profesor.php'; 
    $export_url = 'exportar_profesores_csv.php';
    $label_plural = 'profesores';
    $label_singular = 'profesor';
} else {
    $table = 'Alumnos';
    $curso_field = 'curso_matriculado';
    $edit_url = 'editar_alumno.php';
    $delete_url = 'borrar_alumno.php';
    $add_url = 'nuevo_alumno.php'; 
    $export_url = 'exportar_csv.php';
    $label_plural = 'alumnos';
    $label_singular = 'alumno';
}

$search = $_GET['search'] ?? '';
$curso_filter = $_GET['curso'] ?? '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(nombre LIKE ? OR apellidos LIKE ? OR email LIKE ? OR dni LIKE ?)";
    $like_search = "%$search%";
    $params = array_merge($params, [$like_search, $like_search, $like_search, $like_search]);
}

if (!empty($curso_filter)) {
    $where_clauses[] = "$curso_field = ?";
    $params[] = $curso_filter;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$count_sql = "SELECT COUNT(id) as total FROM $table $where_sql";
$total_result = $db->query($count_sql, $params);
$total_rows = (is_array($total_result) && !empty($total_result)) ? (int)$total_result[0]['total'] : 0;
$total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1; 

$sql = "SELECT id, nombre, apellidos, dni, email, telefono, foto, $curso_field AS curso_actual 
        FROM $table 
        $where_sql 
        ORDER BY id DESC 
        LIMIT $limit OFFSET $offset";
$usuarios = $db->query($sql, $params);
if (!is_array($usuarios)) $usuarios = [];

$cursos_sql = "SELECT DISTINCT $curso_field AS curso FROM $table WHERE $curso_field IS NOT NULL AND $curso_field != '' ORDER BY $curso_field";
$cursos_disponibles = $db->query($cursos_sql);
if (!is_array($cursos_disponibles)) $cursos_disponibles = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Gestión Académica</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css?v=<?php echo time(); ?>" />

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600&display=swap');
        
        :root { --primary: 34 211 238; }
        
        .admin-wrapper { font-family: 'Inter', system-ui, sans-serif; color: #e4e4e7; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
        .table-row { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .table-row:hover { background-color: #1f2937; transform: translateX(4px); }
        .avatar { transition: all 0.3s ease; min-width: 40px; }
        .avatar:hover { transform: scale(1.08); box-shadow: 0 0 0 4px rgb(34 211 238 / 0.3); }
        .badge { font-size: 0.75rem; padding: 2px 10px; border-radius: 9999px; font-weight: 600; white-space: nowrap; }
        
        .tab-link { padding: 10px 24px; border-radius: 12px; font-weight: 600; transition: all 0.2s; text-decoration: none; }
        .tab-link.active { background-color: #06b6d4; color: #09090b; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2); }
        .tab-link.inactive { background-color: #27272a; color: #a1a1aa; border: 1px solid #3f3f46; }
        .tab-link.inactive:hover { background-color: #3f3f46; color: white; }

        /* Estilos específicos para la zona de gráficos */
        .chart-card { background-color: rgba(24, 24, 27, 0.8); backdrop-filter: blur(12px); border: 1px solid #27272a; border-radius: 1.5rem; padding: 1.5rem; }
    </style>
</head>
<body class="text-zinc-200">

    <?php include '../../includes/header.php'; ?>

    <div class="admin-wrapper min-h-screen">
        <div class="max-w-7xl mx-auto pb-10 px-4 sm:px-6 lg:px-8">
            
            <div class="py-6 mt-6">
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6">
                    <div>
                        <h1 class="text-3xl font-semibold title-font tracking-tight text-white m-0">Dashboard General</h1>
                        <p class="text-zinc-400 mt-1">Estadísticas y administración centralizada</p>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        <a href="<?php echo $add_url; ?>" class="flex items-center gap-2 bg-cyan-500 hover:bg-cyan-400 transition-colors text-zinc-950 px-5 py-3 rounded-2xl font-bold shadow-lg shadow-cyan-500/30 no-underline">
                            <i class="fa-solid fa-plus"></i><span class="capitalize">Nuevo <?php echo $label_singular; ?></span>
                        </a>
                        <a href="<?php echo $export_url; ?>" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 transition-colors text-white px-5 py-3 rounded-2xl font-medium shadow-lg shadow-emerald-500/30 no-underline">
                            <i class="fa-solid fa-file-csv"></i><span>CSV</span>
                        </a>
                        <a href="exportar_pdf.php?view=<?php echo $view; ?>&curso=<?php echo urlencode($curso_filter); ?>" target="_blank" class="flex items-center gap-2 bg-rose-500 hover:bg-rose-600 transition-colors text-white px-5 py-3 rounded-2xl font-medium shadow-lg shadow-rose-500/30 no-underline">
                            <i class="fa-solid fa-file-pdf"></i><span>PDF</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="chart-card shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-4"><i class="fa-solid fa-chart-pie text-cyan-400 mr-2"></i>Distribución de Alumnos</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="alumnosCursoChart"></canvas>
                    </div>
                </div>

                <div class="chart-card shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-4"><i class="fa-solid fa-chart-simple text-purple-400 mr-2"></i>Total Registrados</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="usuariosTotalChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="border border-zinc-800 bg-zinc-900/80 backdrop-blur-md rounded-2xl overflow-hidden shadow-2xl">
                <div class="flex gap-3 pt-6 px-4 md:px-8 pb-2 border-b border-zinc-800">
                    <a href="?view=alumnos" class="tab-link <?php echo $view === 'alumnos' ? 'active' : 'inactive'; ?>">
                        <i class="fa-solid fa-user-graduate mr-2"></i> Alumnos
                    </a>
                    <a href="?view=profesores" class="tab-link <?php echo $view === 'profesores' ? 'active' : 'inactive'; ?>">
                        <i class="fa-solid fa-chalkboard-user mr-2"></i> Profesores
                    </a>
                </div>

                <form method="GET" action="index.php" class="px-4 md:px-8 py-4 bg-zinc-900 border-b border-zinc-800 m-0">
                    <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[250px]">
                            <div class="relative">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                       class="w-full bg-zinc-800/80 border border-zinc-700 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 rounded-2xl py-2.5 pl-12 pr-4 text-sm outline-none transition-all text-white"
                                       placeholder="Buscar nombre, DNI, email...">
                                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500"></i>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="flex items-center gap-2 bg-zinc-800/80 border border-zinc-700 rounded-2xl px-4 py-2.5 text-sm">
                                <i class="fa-solid fa-book text-cyan-400"></i>
                                <select name="curso" class="bg-transparent outline-none text-zinc-300 cursor-pointer w-full">
                                    <option value="" class="bg-zinc-800">Todos los cursos</option>
                                    <?php foreach ($cursos_disponibles as $c): ?>
                                        <option value="<?php echo htmlspecialchars($c['curso']); ?>" class="bg-zinc-800" <?php if($curso_filter === $c['curso']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $c['curso'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="bg-zinc-800 hover:bg-cyan-500 transition-colors text-white font-semibold px-6 py-2.5 rounded-2xl flex items-center gap-2 border border-zinc-700 hover:border-cyan-500 cursor-pointer">
                                <i class="fa-solid fa-filter"></i><span>Filtrar</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto bg-transparent">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-zinc-800 bg-zinc-900">
                                <th class="py-4 px-4 md:px-8 text-xs font-medium text-zinc-400 uppercase tracking-widest whitespace-nowrap">Usuario</th>
                                <th class="py-4 px-4 text-xs font-medium text-zinc-400 uppercase tracking-widest hidden sm:table-cell">Curso</th>
                                <th class="py-4 px-4 text-xs font-medium text-zinc-400 uppercase tracking-widest hidden lg:table-cell">Contacto</th>
                                <th class="py-4 px-4 md:px-8 text-center text-xs font-medium text-zinc-400 uppercase tracking-widest whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800 bg-transparent">
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-zinc-500">No se han encontrado <?php echo $label_plural; ?>.</td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $colores_avatar = ['bg-cyan-500', 'bg-purple-500', 'bg-emerald-500', 'bg-blue-500', 'bg-pink-500', 'bg-amber-500', 'bg-rose-500'];
                                foreach ($usuarios as $usuario): 
                                    $inicial = mb_strtoupper(mb_substr($usuario['nombre'], 0, 1));
                                    $color_idx = $usuario['id'] % count($colores_avatar);
                                    $color_bg = $colores_avatar[$color_idx];
                                    $curso_raw = $usuario['curso_actual'] ?? 'Sin asignar';
                                    $curso_text = str_replace('_', ' ', $curso_raw);
                                    
                                    $badge_color = 'bg-zinc-600';
                                    if (strpos($curso_raw, 'DAW') !== false) $badge_color = 'bg-blue-500';
                                    elseif (strpos($curso_raw, 'ASIR') !== false) $badge_color = 'bg-indigo-500';
                                    elseif (strpos($curso_raw, 'SMR') !== false) $badge_color = 'bg-emerald-500';
                                ?>
                                    <tr class="table-row border-b border-zinc-800 last:border-none">
                                        <td class="px-4 md:px-8 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                <?php if (!empty($usuario['foto']) && strpos($usuario['foto'], 'ui-avatars') === false): ?>
                                                    <img src="../../<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto" class="avatar w-12 h-12 rounded-2xl object-cover shadow-inner">
                                                <?php else: ?>
                                                    <div class="avatar w-12 h-12 <?php echo $color_bg; ?> rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-inner"><?php echo $inicial; ?></div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="font-bold text-white text-base mb-1"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></div>
                                                    <div class="text-zinc-500 font-mono text-xs">ID: #<?php echo $usuario['id']; ?> | DNI: <?php echo htmlspecialchars($usuario['dni']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 hidden sm:table-cell">
                                            <span class="badge <?php echo $badge_color; ?> text-white tracking-wide"><?php echo htmlspecialchars($curso_text); ?></span>
                                        </td>
                                        <td class="px-4 py-4 hidden lg:table-cell">
                                            <div class="text-cyan-400 text-sm mb-1 break-all"><?php echo htmlspecialchars($usuario['email']); ?></div>
                                            <div class="text-zinc-400 font-mono text-xs"><i class="fa-solid fa-phone text-zinc-600 mr-1"></i> <?php echo htmlspecialchars($usuario['telefono']); ?></div>
                                        </td>
                                        <td class="px-4 md:px-8 py-4 whitespace-nowrap text-center">
                                            <div class="flex justify-center gap-2">
                                                <a href="<?php echo $edit_url; ?>?id=<?php echo $usuario['id']; ?>" class="bg-zinc-800 hover:bg-blue-600 hover:text-white p-2.5 rounded-xl transition-all text-zinc-400"><i class="fa-solid fa-pen text-sm"></i></a>
                                                <a href="#" onclick="confirmarBorrado(event, '<?php echo $delete_url; ?>?id=<?php echo $usuario['id']; ?>', '<?php echo addslashes($usuario['nombre'] . ' ' . $usuario['apellidos']); ?>')" class="bg-zinc-800 hover:bg-red-600 hover:text-white p-2.5 rounded-xl transition-all text-zinc-400"><i class="fa-solid fa-trash text-sm"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 0): ?>
                <div class="px-4 md:px-8 py-5 flex flex-col sm:flex-row items-center justify-between text-sm gap-4 bg-zinc-900 border-t border-zinc-800">
                    <div class="text-zinc-400">
                        Mostrando <span class="font-medium text-white"><?php echo ($total_rows > 0) ? $offset + 1 : 0; ?>-<?php echo min($offset + $limit, $total_rows); ?></span> de <span class="font-medium text-white"><?php echo $total_rows; ?></span>
                    </div>
                    <div class="flex gap-2">
                        <?php 
                        $q = $_GET; 
                        if ($page > 1): $q['page'] = $page - 1; ?>
                            <a href="index.php?<?php echo http_build_query($q); ?>" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php else: ?>
                            <button disabled class="px-4 py-2 bg-zinc-900 text-zinc-600 rounded-xl"><i class="fa-solid fa-chevron-left"></i></button>
                        <?php endif; ?>

                        <?php for($i = 1; $i <= $total_pages; $i++): $q['page'] = $i; ?>
                            <?php if ($i == $page): ?>
                                <span class="px-4 py-2 bg-cyan-500 text-zinc-900 font-semibold rounded-xl"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="index.php?<?php echo http_build_query($q); ?>" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): $q['page'] = $page + 1; ?>
                            <a href="index.php?<?php echo http_build_query($q); ?>" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php else: ?>
                            <button disabled class="px-4 py-2 bg-zinc-900 text-zinc-600 rounded-xl"><i class="fa-solid fa-chevron-right"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    // 1. Script de SweetAlert2 (Para borrar)
    function confirmarBorrado(event, url, nombreUsuario) {
        event.preventDefault();
        Swal.fire({
            title: '¿Estás seguro?',
            html: `Vas a eliminar a <strong>${nombreUsuario}</strong>.`,
            icon: 'warning', iconColor: '#ef4444', background: '#18181b', color: '#e4e4e7',
            showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#3f3f46',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
            customClass: { popup: 'border border-zinc-800 rounded-3xl shadow-2xl', confirmButton: 'rounded-xl font-bold px-6', cancelButton: 'rounded-xl font-bold px-6' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Eliminando...', background: '#18181b', color: '#e4e4e7', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                window.location.href = url; 
            }
        });
    }

    // 2. Scripts de Chart.js (Gráficos)
    // Configuración global para tema oscuro
    Chart.defaults.color = '#a1a1aa';
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";

    // Gráfico 1: Alumnos por Curso (Doughnut)
    const ctxCursos = document.getElementById('alumnosCursoChart').getContext('2d');
    new Chart(ctxCursos, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($cursosNombres); ?>,
            datasets: [{
                data: <?php echo json_encode($cursosTotales); ?>,
                backgroundColor: ['#06b6d4', '#10b981', '#f43f5e', '#8b5cf6', '#f59e0b', '#3b82f6'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { position: 'right', labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' } }
            }
        }
    });

    // Gráfico 2: Total Usuarios (Barras)
    const ctxTotal = document.getElementById('usuariosTotalChart').getContext('2d');
    new Chart(ctxTotal, {
        type: 'bar',
        data: {
            labels: ['Alumnos', 'Profesores'],
            datasets: [{
                label: 'Usuarios Registrados',
                data: [<?php echo $totalAlumnosChart; ?>, <?php echo $totalProfesoresChart; ?>],
                backgroundColor: ['#06b6d4', '#8b5cf6'],
                borderRadius: 8,
                borderWidth: 0,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#27272a', drawBorder: false }, // Líneas guía sutiles
                    ticks: { stepSize: 1 } // Para que no muestre 1.5 alumnos
                },
                x: {
                    grid: { display: false, drawBorder: false }
                }
            }
        }
    });
    </script>
</body>
</html>