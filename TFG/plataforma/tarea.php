<?php
session_start();

if (empty($_SESSION['user_id']) || !in_array($_SESSION['tipo_usuario'], ['profesor', 'alumno'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../config/db_pdo.php';
$db = DB::open();

$rol = $_SESSION['tipo_usuario'];
$id_recurso = (int)($_GET['id'] ?? 0);

$recurso = $db->query("SELECT * FROM Recursos WHERE id = ?", [$id_recurso]);
if (empty($recurso)) {
    exit("Tarea no encontrada.");
}
$tarea = $recurso[0];

// Añadimos "?string" para decirle a Intelephense que es un texto (o null)
function calcularTiempoRestante(?string $fecha_limite) {
    if (!$fecha_limite) return null;
    $ahora = new DateTime();
    $limite = new DateTime($fecha_limite);
    $diferencia = $ahora->diff($limite);
    
    if ($limite < $ahora) {
        $texto = "La tarea está retrasada por ";
        if ($diferencia->d > 0) $texto .= $diferencia->d . " días y ";
        $texto .= $diferencia->h . " horas";
        return ['texto' => $texto, 'estado' => 'retrasada'];
    } else {
        $texto = "Quedan ";
        if ($diferencia->d > 0) $texto .= $diferencia->d . " días y ";
        $texto .= $diferencia->h . " horas";
        $estado = ($diferencia->d == 0) ? 'urgente' : 'normal';
        return ['texto' => $texto, 'estado' => $estado];
    }
}

$tiempo_restante = calcularTiempoRestante($tarea['fecha_limite'] ?? null);

// ==========================================
// 🧑‍🎓 LÓGICA ALUMNO: SUBIR ENTREGA
// ==========================================
$error_subida = null;

if ($rol === 'alumno' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'entregar') {
    $id_alumno = $_SESSION['user_id'];
    $comentario = $_POST['comentario_entrega'] ?? '';
    $archivo_url = null;

    if (isset($_FILES['archivo_entrega']) && $_FILES['archivo_entrega']['error'] === UPLOAD_ERR_OK) {
        $tamano_maximo_bytes = ($tarea['tamano_maximo'] ?? 5) * 1024 * 1024; 
        if ($_FILES['archivo_entrega']['size'] > $tamano_maximo_bytes) {
            $error_subida = "El archivo es demasiado grande. El tamaño máximo permitido es " . ($tarea['tamano_maximo'] ?? 5) . " MB.";
        } else {
            $ruta_dest = '../assets/uploads/entregas/';
            if (!is_dir($ruta_dest)) mkdir($ruta_dest, 0777, true);
            
            $nombre_orig = pathinfo($_FILES['archivo_entrega']['name'], PATHINFO_FILENAME);
            $ext = pathinfo($_FILES['archivo_entrega']['name'], PATHINFO_EXTENSION);
            $nuevo_nombre = time() . '_usr' . $id_alumno . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $nombre_orig) . '.' . $ext;
            $ruta_final = $ruta_dest . $nuevo_nombre;

            if (move_uploaded_file($_FILES['archivo_entrega']['tmp_name'], $ruta_final)) {
                $archivo_url = 'assets/uploads/entregas/' . $nuevo_nombre;
                $db->query("INSERT INTO Entregas (recurso_id, alumno_id, archivo_url, comentario) VALUES (?, ?, ?, ?)", 
                           [$id_recurso, $id_alumno, $archivo_url, $comentario]);
                header("Location: tarea.php?id=" . $id_recurso . "&msg=exito");
                exit;
            } else {
                $error_subida = "Hubo un error al guardar el archivo en el servidor.";
            }
        }
    }
}

// ==========================================
// 👨‍🏫 LÓGICA PROFESOR: CALIFICAR
// ==========================================
if ($rol === 'profesor' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'calificar') {
    $id_entrega = (int)$_POST['id_entrega'];
    $id_alumno_calificar = (int)$_POST['id_alumno'];
    $nota = $_POST['nota'];
    $comentario_profe = $_POST['comentario_profe'];
    
    if ($id_entrega > 0) {
        // Actualizar entrega existente
        $db->query("UPDATE Entregas SET nota = ?, comentario_profe = ?, fecha_calificacion = CURRENT_TIMESTAMP WHERE id = ?", 
                   [$nota, $comentario_profe, $id_entrega]);
    } else {
        // El alumno no entregó archivo, pero el profe le pone nota (ej: 0 o NP)
        $db->query("INSERT INTO Entregas (recurso_id, alumno_id, nota, comentario_profe, fecha_calificacion) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)", 
                   [$id_recurso, $id_alumno_calificar, $nota, $comentario_profe]);
    }
    
    header("Location: tarea.php?id=" . $id_recurso . "&msg=calificado");
    exit;
}

// ==========================================
// 📥 OBTENER DATOS PARA LA VISTA
// ==========================================
$entrega_alumno = null;
$todas_entregas = [];
$total_pages = 1; 
$page = 1;

if ($rol === 'alumno') {
    $res = $db->query("SELECT * FROM Entregas WHERE recurso_id = ? AND alumno_id = ?", [$id_recurso, $_SESSION['user_id']]);
    if (!empty($res)) $entrega_alumno = $res[0];
} else {
    // === LÓGICA DE FILTROS, ORDENACIÓN Y PAGINACIÓN DEL PROFESOR ===
    $estado_filtro = $_GET['estado'] ?? 'todos';
    $orden_filtro = $_GET['orden'] ?? 'nombre_asc';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 10; // Alumnos por página
    $offset = ($page - 1) * $limit;

    // 1. Averiguar de qué curso es esta asignatura
    $curso_tarea = null;
    $asig_info = $db->query("SELECT curso FROM Asignaturas WHERE nombre = ?", [$tarea['asignatura']]);
    if (!empty($asig_info)) {
        $curso_tarea = $asig_info[0]['curso'];
    }

    $params = [$id_recurso];
    $where_sql = "";

    // APLICAR FILTRO DE CURSO REAL
    if ($curso_tarea) {
        $where_sql .= " AND a.curso_matriculado = ?";
        $params[] = $curso_tarea;
    }

    // Filtros de estado
    if ($estado_filtro === 'entregado') {
        $where_sql .= " AND e.id IS NOT NULL AND e.archivo_url IS NOT NULL";
    } elseif ($estado_filtro === 'no_entregado') {
        $where_sql .= " AND (e.id IS NULL OR e.archivo_url IS NULL)";
    } elseif ($estado_filtro === 'calificado') {
        $where_sql .= " AND e.nota IS NOT NULL AND e.nota != ''";
    } elseif ($estado_filtro === 'sin_calificar') {
        $where_sql .= " AND e.id IS NOT NULL AND e.archivo_url IS NOT NULL AND (e.nota IS NULL OR e.nota = '')";
    }

    // Ordenación
    $order_sql = "a.apellidos ASC, a.nombre ASC"; // Por defecto
    if ($orden_filtro === 'fecha_desc') {
        $order_sql = "e.fecha_entrega DESC, a.apellidos ASC";
    } elseif ($orden_filtro === 'fecha_asc') {
        $order_sql = "e.fecha_entrega ASC, a.apellidos ASC";
    } elseif ($orden_filtro === 'nota_desc') {
        $order_sql = "CAST(e.nota AS DECIMAL) DESC, a.apellidos ASC";
    }

    // Contar totales para la paginación
    $count_sql = "SELECT COUNT(a.id) as total 
                  FROM Alumnos a 
                  LEFT JOIN Entregas e ON a.id = e.alumno_id AND e.recurso_id = ? 
                  WHERE 1=1 $where_sql";
    $total_res = $db->query($count_sql, $params);
    $total_rows = !empty($total_res) ? (int)$total_res[0]['total'] : 0;
    $total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

    // Obtener los datos paginados
    $sql = "SELECT a.id as alumno_id, a.nombre, a.apellidos, 
                   e.id as entrega_id, e.fecha_entrega, e.archivo_url, e.nota, e.comentario, e.comentario_profe 
            FROM Alumnos a 
            LEFT JOIN Entregas e ON a.id = e.alumno_id AND e.recurso_id = ? 
            WHERE 1=1 $where_sql 
            ORDER BY $order_sql 
            LIMIT $limit OFFSET $offset";
    
    $todas_entregas = $db->query($sql, $params);
    if (!$todas_entregas) $todas_entregas = [];
}

// Añadimos "?string" para Intelephense
function mostrarNombreLimpio(?string $ruta) {
    if (!$ruta) return '';
    $nombre = basename($ruta);
    return preg_replace('/^[0-9]+_(usr[0-9]+_)?/', '', $nombre);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea: <?php echo htmlspecialchars($tarea['titulo']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        .moodle-table th, .moodle-table td { padding: 15px 20px; border-bottom: 1px solid #27272a; text-align: left; }
        .moodle-table th { background-color: #18181b; color: #a1a1aa; font-weight: 500; width: 250px; }
        .moodle-table td { background-color: #111827; color: #e4e4e7; }
        .row-success td { background-color: rgba(16, 185, 129, 0.05); }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-200">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto py-10 px-6">
        <a href="aula.php?asignatura=<?php echo urlencode($tarea['asignatura']); ?>&unidad=<?php echo urlencode($tarea['unidad']); ?>" class="text-cyan-500 hover:text-cyan-400 no-underline mb-6 inline-block transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Volver al aula
        </a>

        <?php if($error_subida): ?>
            <div class="bg-red-500/20 border border-red-500/50 text-red-200 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                <span class="font-medium"><?php echo htmlspecialchars($error_subida); ?></span>
            </div>
        <?php endif; ?>

        <div class="flex items-center gap-4 mb-8 border-b border-zinc-800 pb-6">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex justify-center items-center text-2xl border border-blue-500/30 text-blue-400">
                <i class="fa-solid fa-file-arrow-up"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white m-0"><?php echo htmlspecialchars($tarea['titulo']); ?></h1>
                <p class="text-zinc-400 m-0 text-sm">Práctica Evaluable • <?php echo htmlspecialchars($tarea['asignatura']); ?></p>
            </div>
        </div>

        <?php if(!empty($tarea['archivo_url'])): ?>
        <div class="mb-10 bg-zinc-900 border border-zinc-800 rounded-2xl p-5 shadow-lg">
            <h3 class="text-sm text-zinc-400 uppercase tracking-wider mb-3 font-semibold">Documento adjunto</h3>
            <a href="../<?php echo $tarea['archivo_url']; ?>" target="_blank" class="flex items-center gap-3 bg-zinc-800 hover:bg-zinc-700 p-4 rounded-xl w-max transition-colors no-underline text-white break-all md:break-normal">
                <i class="fa-regular fa-file-pdf text-red-400 text-xl shrink-0"></i>
                <span><?php echo htmlspecialchars(mostrarNombreLimpio($tarea['archivo_url'])); ?></span>
            </a>
        </div>
        <?php endif; ?>

        <?php if ($rol === 'alumno'): ?>
            <h2 class="text-xl text-white mb-4">Estado de la entrega</h2>
            
            <div class="border border-zinc-800 rounded-2xl overflow-hidden mb-8 shadow-xl">
                <table class="w-full moodle-table border-collapse">
                    <tr class="<?php echo ($entrega_alumno && $entrega_alumno['archivo_url']) ? 'row-success' : ''; ?>">
                        <th>Estado de la entrega</th>
                        <td>
                            <?php if($entrega_alumno && $entrega_alumno['archivo_url']): ?>
                                <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-check mr-2"></i>Enviado para calificar</span>
                            <?php else: ?>
                                <span class="text-zinc-400">No se ha enviado nada en esta tarea</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th>Estado de la calificación</th>
                        <td>
                            <?php if(!$entrega_alumno): ?>
                                <span class="text-zinc-500">-</span>
                            <?php elseif($entrega_alumno['nota']): ?>
                                <span class="text-emerald-400 font-semibold">Calificado</span>
                            <?php else: ?>
                                <span class="text-amber-400 font-medium">Sin calificar</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if($tarea['fecha_limite']): ?>
                        <tr>
                            <th>Fecha límite</th>
                            <td><?php echo date('l, d \d\e F \d\e Y, H:i', strtotime($tarea['fecha_limite'])); ?></td>
                        </tr>
                        <?php if(!$entrega_alumno && $tiempo_restante): ?>
                        <tr>
                            <th>Tiempo restante</th>
                            <td>
                                <?php 
                                    $clase_texto = 'text-zinc-300';
                                    if($tiempo_restante['estado'] === 'retrasada') $clase_texto = 'text-red-400 font-bold';
                                    if($tiempo_restante['estado'] === 'urgente') $clase_texto = 'text-amber-400 font-bold';
                                ?>
                                <span class="<?php echo $clase_texto; ?>"><?php echo $tiempo_restante['texto']; ?></span>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if($entrega_alumno && $entrega_alumno['archivo_url']): ?>
                    <tr>
                        <th>Fecha de entrega</th>
                        <td>
                            <?php echo date('d/m/Y - H:i', strtotime($entrega_alumno['fecha_entrega'])); ?>
                            <?php 
                            if($tarea['fecha_limite'] && new DateTime($entrega_alumno['fecha_entrega']) > new DateTime($tarea['fecha_limite'])) {
                                echo ' <span class="text-red-400 font-medium text-xs ml-2">(Entregado con retraso)</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Archivos enviados</th>
                        <td>
                            <a href="../<?php echo $entrega_alumno['archivo_url']; ?>" target="_blank" class="text-cyan-400 hover:text-cyan-300 no-underline flex items-start gap-2 break-all md:break-normal transition-colors">
                                <i class="fa-solid fa-file-lines mt-1 shrink-0"></i> 
                                <span><?php echo htmlspecialchars(mostrarNombreLimpio($entrega_alumno['archivo_url'])); ?></span>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>Comentarios al profesor</th>
                        <td class="italic text-zinc-400"><?php echo !empty($entrega_alumno['comentario']) ? htmlspecialchars($entrega_alumno['comentario']) : 'Sin comentarios.'; ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if($entrega_alumno && $entrega_alumno['nota']): ?>
                <h2 class="text-xl text-white mb-4 mt-10">Comentario del Profesor</h2>
                <div class="border border-zinc-800 rounded-2xl overflow-hidden mb-8 shadow-xl">
                    <table class="w-full moodle-table border-collapse">
                        <tr>
                            <th>Calificación</th>
                            <td class="text-xl font-bold text-white"><?php echo htmlspecialchars($entrega_alumno['nota']); ?></td>
                        </tr>
                        <tr>
                            <th>Calificado el</th>
                            <td><?php echo date('d/m/Y - H:i', strtotime($entrega_alumno['fecha_calificacion'])); ?></td>
                        </tr>
                        <tr>
                            <th>Comentarios de retroalimentación</th>
                            <td class="text-zinc-300"><?php echo nl2br(htmlspecialchars($entrega_alumno['comentario_profe'])); ?></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <?php if(!$entrega_alumno || !$entrega_alumno['archivo_url']): ?>
                <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <h3 class="text-lg text-white mb-1">Añadir entrega</h3>
                    <p class="text-zinc-500 text-sm mb-6"><i class="fa-solid fa-circle-info mr-1"></i> Tamaño máximo permitido: <?php echo $tarea['tamano_maximo'] ?? 5; ?> MB.</p>
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="entregar">
                        <div class="mb-4">
                            <label class="block text-zinc-400 mb-2">Sube tu archivo (PDF, ZIP...)</label>
                            <input type="file" name="archivo_entrega" class="w-full bg-zinc-800 border border-zinc-700 text-white p-3 rounded-xl outline-none focus:border-cyan-500 transition-colors" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-zinc-400 mb-2">Comentarios (Opcional)</label>
                            <textarea name="comentario_entrega" class="w-full bg-zinc-800 border border-zinc-700 text-white p-3 rounded-xl outline-none focus:border-cyan-500 transition-colors" rows="3" placeholder="¿Algo que deba saber el profesor?"></textarea>
                        </div>
                        <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold py-3 px-6 rounded-xl transition-colors shadow-lg shadow-cyan-500/20 border-none cursor-pointer">
                            Subir Entrega
                        </button>
                    </form>
                </div>
            <?php endif; ?>

        <?php elseif ($rol === 'profesor'): ?>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-4">
                <h2 class="text-xl text-white m-0">Calificador y Entregas</h2>
                <?php if($tarea['fecha_limite']): ?>
                    <span class="text-sm bg-zinc-800 text-zinc-300 px-3 py-1.5 rounded-lg border border-zinc-700">
                        <i class="fa-regular fa-calendar-xmark text-cyan-400 mr-1"></i> Límite: <?php echo date('d/m/Y H:i', strtotime($tarea['fecha_limite'])); ?>
                    </span>
                <?php endif; ?>
            </div>

            <form method="GET" class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4 mb-6 shadow-lg flex flex-wrap gap-4 items-end">
                <input type="hidden" name="id" value="<?php echo $id_recurso; ?>">
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-zinc-400 mb-2 uppercase tracking-wider font-semibold">Estado de la entrega</label>
                    <select name="estado" class="w-full bg-zinc-800 border border-zinc-700 text-white p-2.5 rounded-xl outline-none focus:border-cyan-500">
                        <option value="todos" <?php echo $estado_filtro === 'todos' ? 'selected' : ''; ?>>Todos los alumnos</option>
                        <option value="entregado" <?php echo $estado_filtro === 'entregado' ? 'selected' : ''; ?>>Solo entregados</option>
                        <option value="no_entregado" <?php echo $estado_filtro === 'no_entregado' ? 'selected' : ''; ?>>No entregados</option>
                        <option value="sin_calificar" <?php echo $estado_filtro === 'sin_calificar' ? 'selected' : ''; ?>>Necesitan calificación</option>
                        <option value="calificado" <?php echo $estado_filtro === 'calificado' ? 'selected' : ''; ?>>Ya calificados</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-zinc-400 mb-2 uppercase tracking-wider font-semibold">Ordenar por</label>
                    <select name="orden" class="w-full bg-zinc-800 border border-zinc-700 text-white p-2.5 rounded-xl outline-none focus:border-cyan-500">
                        <option value="nombre_asc" <?php echo $orden_filtro === 'nombre_asc' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                        <option value="fecha_asc" <?php echo $orden_filtro === 'fecha_asc' ? 'selected' : ''; ?>>Fecha (Más antiguos primero)</option>
                        <option value="fecha_desc" <?php echo $orden_filtro === 'fecha_desc' ? 'selected' : ''; ?>>Fecha (Más recientes primero)</option>
                        <option value="nota_desc" <?php echo $orden_filtro === 'nota_desc' ? 'selected' : ''; ?>>Nota (De mayor a menor)</option>
                    </select>
                </div>

                <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold px-6 py-2.5 rounded-xl transition-colors border-none cursor-pointer">
                    <i class="fa-solid fa-filter mr-1"></i> Aplicar
                </button>
            </form>
            
            <div class="bg-zinc-900 border border-zinc-800 rounded-t-2xl shadow-xl overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-zinc-800/50 border-b border-zinc-700">
                            <th class="p-4 text-zinc-400 font-medium">Alumno</th>
                            <th class="p-4 text-zinc-400 font-medium whitespace-nowrap">Fecha de entrega</th>
                            <th class="p-4 text-zinc-400 font-medium w-1/3">Archivo</th>
                            <th class="p-4 text-zinc-400 font-medium whitespace-nowrap">Nota</th>
                            <th class="p-4 text-zinc-400 font-medium text-center whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        <?php if(empty($todas_entregas)): ?>
                            <tr><td colspan="5" class="p-8 text-center text-zinc-500">No hay alumnos que coincidan con los filtros.</td></tr>
                        <?php else: ?>
                            <?php foreach($todas_entregas as $ent): ?>
                            <tr class="hover:bg-zinc-800/30 transition-colors <?php echo empty($ent['archivo_url']) ? 'opacity-70' : ''; ?>">
                                <td class="p-4 font-medium text-white">
                                    <?php echo htmlspecialchars($ent['apellidos'] . ', ' . $ent['nombre']); ?>
                                    <?php if(!empty($ent['comentario'])): ?>
                                        <div class="text-xs text-zinc-500 mt-1 break-words"><i class="fa-solid fa-comment mr-1"></i> <?php echo htmlspecialchars($ent['comentario']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm whitespace-nowrap">
                                    <?php if($ent['fecha_entrega']): ?>
                                        <div class="text-zinc-400"><?php echo date('d/m/Y H:i', strtotime($ent['fecha_entrega'])); ?></div>
                                        <?php 
                                            if($tarea['fecha_limite'] && new DateTime($ent['fecha_entrega']) > new DateTime($tarea['fecha_limite'])) {
                                                echo '<div class="text-red-400 text-xs font-semibold mt-0.5">Fuera de plazo</div>';
                                            }
                                        ?>
                                    <?php else: ?>
                                        <span class="text-zinc-600">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 break-words">
                                    <?php if($ent['archivo_url']): ?>
                                        <a href="../<?php echo $ent['archivo_url']; ?>" target="_blank" class="text-cyan-400 hover:text-cyan-300 transition-colors no-underline flex items-start gap-2">
                                            <i class="fa-solid fa-file-lines mt-1 shrink-0"></i> 
                                            <span><?php echo htmlspecialchars(mostrarNombreLimpio($ent['archivo_url'])); ?></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="bg-zinc-800 text-zinc-400 px-2.5 py-1 rounded-md text-xs font-medium">Sin entrega</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <?php if($ent['nota']): ?>
                                        <span class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 px-3 py-1 rounded-full text-sm font-bold shadow-sm"><?php echo htmlspecialchars($ent['nota']); ?></span>
                                    <?php else: ?>
                                        <span class="text-amber-400 text-sm font-medium">Sin calificar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <button class="bg-blue-500 hover:bg-blue-400 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors btn-calificar shadow-lg shadow-blue-500/20 border-none cursor-pointer" 
                                            data-id="<?php echo $ent['entrega_id'] ?? 0; ?>" 
                                            data-idalumno="<?php echo $ent['alumno_id']; ?>" 
                                            data-nombre="<?php echo htmlspecialchars($ent['nombre']); ?>"
                                            data-nota="<?php echo htmlspecialchars($ent['nota'] ?? ''); ?>"
                                            data-comentario="<?php echo htmlspecialchars($ent['comentario_profe'] ?? ''); ?>">
                                        <i class="fa-solid fa-pen mr-1"></i> <?php echo $ent['nota'] ? 'Modificar' : 'Calificar'; ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="bg-zinc-900 border border-zinc-800 border-t-0 rounded-b-2xl p-4 flex items-center justify-between shadow-xl">
                <span class="text-zinc-400 text-sm">Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>
                <div class="flex gap-2">
                    <?php 
                        $q_params = $_GET;
                        
                        if ($page > 1) {
                            $q_params['page'] = $page - 1;
                            echo '<a href="?'.http_build_query($q_params).'" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-sm transition-colors no-underline">Anterior</a>';
                        }
                        
                        if ($page < $total_pages) {
                            $q_params['page'] = $page + 1;
                            echo '<a href="?'.http_build_query($q_params).'" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white rounded-xl text-sm transition-colors no-underline">Siguiente</a>';
                        }
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="fixed inset-0 bg-zinc-950/90 backdrop-blur-sm z-50 hidden flex-col justify-center items-center opacity-0 transition-opacity" id="modalCalificar">
                <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-8 w-full max-w-md shadow-2xl transform transition-transform scale-95" id="modalCalificarCard">
                    <h2 class="text-xl text-white mb-2 font-semibold">Calificar a <span id="nombre_calificar" class="text-cyan-400"></span></h2>
                    <form action="" method="POST">
                        <input type="hidden" name="accion" value="calificar">
                        <input type="hidden" name="id_entrega" id="id_entrega_calificar">
                        <input type="hidden" name="id_alumno" id="id_alumno_calificar">
                        
                        <div class="mb-4 mt-6">
                            <label class="block text-zinc-400 mb-2 font-medium">Calificación (Nota, AP, NP...)</label>
                            <input type="text" name="nota" id="input_nota" class="w-full bg-zinc-800 border border-zinc-700 text-white p-3 rounded-xl outline-none focus:border-cyan-500 transition-colors" required>
                        </div>
                        <div class="mb-6">
                            <label class="block text-zinc-400 mb-2 font-medium">Comentarios de retroalimentación</label>
                            <textarea name="comentario_profe" id="input_comentario" class="w-full bg-zinc-800 border border-zinc-700 text-white p-3 rounded-xl outline-none focus:border-cyan-500 transition-colors" rows="4"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold py-3 rounded-xl transition-colors border-none cursor-pointer">Guardar Nota</button>
                            <button type="button" id="btnCerrarCalificar" class="flex-1 bg-zinc-800 hover:bg-zinc-700 text-white font-bold py-3 rounded-xl transition-colors border-none cursor-pointer">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                const modalCalificar = document.getElementById('modalCalificar');
                const modalCalificarCard = document.getElementById('modalCalificarCard');
                
                document.getElementById('btnCerrarCalificar').addEventListener('click', () => {
                    modalCalificarCard.classList.remove('scale-100');
                    modalCalificar.classList.remove('opacity-100');
                    setTimeout(() => {
                        modalCalificar.classList.add('hidden');
                        modalCalificar.classList.remove('flex');
                    }, 200);
                });

                document.querySelectorAll('.btn-calificar').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.getElementById('id_entrega_calificar').value = this.dataset.id;
                        document.getElementById('id_alumno_calificar').value = this.dataset.idalumno;
                        document.getElementById('nombre_calificar').innerText = this.dataset.nombre;
                        document.getElementById('input_nota').value = this.dataset.nota;
                        document.getElementById('input_comentario').value = this.dataset.comentario;
                        
                        modalCalificar.classList.remove('hidden');
                        modalCalificar.classList.add('flex');
                        setTimeout(() => {
                            modalCalificar.classList.add('opacity-100');
                            modalCalificarCard.classList.add('scale-100');
                        }, 10);
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>