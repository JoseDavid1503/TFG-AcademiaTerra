<?php
session_start();

// 🛡️ CONTROL DE SEGURIDAD
if (empty($_SESSION['user_id']) || !in_array($_SESSION['tipo_usuario'], ['profesor', 'alumno'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/config.php';
require_once '../config/db_pdo.php';
$db = DB::open();

$rol = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['user_id'];
$nombre_asignatura = $_GET['asignatura'] ?? 'Asignatura no especificada';
$unidad_actual = $_GET['unidad'] ?? 'general';

$pestañas = [
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

if (!array_key_exists($unidad_actual, $pestañas)) {
    $unidad_actual = 'general';
}

// ==========================================
// ⚙️ LÓGICA DE ACCIONES DEL PROFESOR
// ==========================================
$entregas_pendientes_aula = 0;

if ($rol === 'profesor') {
    $res_pendientes = $db->query("
        SELECT COUNT(e.id) as pendientes 
        FROM Entregas e 
        JOIN Recursos r ON e.recurso_id = r.id 
        WHERE r.asignatura = ? AND (e.nota IS NULL OR e.nota = '')
    ", [$nombre_asignatura]);
    $entregas_pendientes_aula = (int)($res_pendientes[0]['pendientes'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'nuevo_recurso') {
        $tipo = $_POST['tipo_recurso'];
        $titulo = $_POST['titulo_recurso'];
        $archivo_url = null;
        
        $fecha_limite = null;
        $tamano_maximo = null;
        if (in_array($tipo, ['tarea', 'tarea_opcional', 'examen'])) {
            $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null;
            $tamano_maximo = !empty($_POST['tamano_maximo']) ? (int)$_POST['tamano_maximo'] : null;
        }

        if (isset($_FILES['archivo_recurso']) && $_FILES['archivo_recurso']['error'] === UPLOAD_ERR_OK) {
            $ruta_dest = '../assets/uploads/recursos/';
            if (!is_dir($ruta_dest)) mkdir($ruta_dest, 0777, true);

            $nombre_orig = pathinfo($_FILES['archivo_recurso']['name'], PATHINFO_FILENAME);
            $ext = pathinfo($_FILES['archivo_recurso']['name'], PATHINFO_EXTENSION);
            $nuevo_nombre = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $nombre_orig) . '.' . $ext;
            $ruta_final = $ruta_dest . $nuevo_nombre;

            if (move_uploaded_file($_FILES['archivo_recurso']['tmp_name'], $ruta_final)) {
                $archivo_url = 'assets/uploads/recursos/' . $nuevo_nombre;
            }
        }
        
        $db->query("INSERT INTO Recursos (asignatura, unidad, tipo, titulo, archivo_url, visible, fecha_limite, tamano_maximo) VALUES (?, ?, ?, ?, ?, 1, ?, ?)", 
                   [$nombre_asignatura, $unidad_actual, $tipo, $titulo, $archivo_url, $fecha_limite, $tamano_maximo]);
        
        header("Location: aula.php?asignatura=" . urlencode($nombre_asignatura) . "&unidad=" . urlencode($unidad_actual));
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar_recurso') {
        $id_editar = (int)$_POST['id_recurso_edit'];
        $tipo_editar = $_POST['tipo_recurso_edit'];
        $titulo_editar = $_POST['titulo_recurso_edit'];
        
        $fecha_limite_edit = null;
        $tamano_maximo_edit = null;
        if (in_array($tipo_editar, ['tarea', 'tarea_opcional', 'examen'])) {
            $fecha_limite_edit = !empty($_POST['fecha_limite_edit']) ? $_POST['fecha_limite_edit'] : null;
            $tamano_maximo_edit = !empty($_POST['tamano_maximo_edit']) ? (int)$_POST['tamano_maximo_edit'] : null;
        }
        
        $db->query("UPDATE Recursos SET tipo = ?, titulo = ?, fecha_limite = ?, tamano_maximo = ? WHERE id = ?", 
                   [$tipo_editar, $titulo_editar, $fecha_limite_edit, $tamano_maximo_edit, $id_editar]);
        header("Location: aula.php?asignatura=" . urlencode($nombre_asignatura) . "&unidad=" . urlencode($unidad_actual));
        exit;
    }

    if (isset($_GET['accion_recurso']) && $_GET['accion_recurso'] === 'borrar') {
        $id_recurso = (int)$_GET['id_recurso'];
        $res = $db->query("SELECT archivo_url FROM Recursos WHERE id = ?", [$id_recurso]);
        if (!empty($res) && !empty($res[0]['archivo_url'])) {
            $archivo_a_borrar = '../' . $res[0]['archivo_url'];
            if (file_exists($archivo_a_borrar)) unlink($archivo_a_borrar);
        }
        $db->query("DELETE FROM Recursos WHERE id = ?", [$id_recurso]);
        header("Location: aula.php?asignatura=" . urlencode($nombre_asignatura) . "&unidad=" . urlencode($unidad_actual));
        exit;
    }

    if (isset($_GET['accion_recurso']) && $_GET['accion_recurso'] === 'toggle_visibilidad') {
        $id_recurso = (int)$_GET['id_recurso'];
        $estado_actual = $db->query("SELECT visible FROM Recursos WHERE id = ?", [$id_recurso]);
        if (!empty($estado_actual)) {
            $nuevo_estado = ($estado_actual[0]['visible'] == 1) ? 0 : 1;
            $db->query("UPDATE Recursos SET visible = ? WHERE id = ?", [$nuevo_estado, $id_recurso]);
        }
        header("Location: aula.php?asignatura=" . urlencode($nombre_asignatura) . "&unidad=" . urlencode($unidad_actual));
        exit;
    }
}

// ==========================================
// 📥 OBTENER LOS DATOS PARA LA VISTA
// ==========================================
$recursos = $db->query("SELECT * FROM Recursos WHERE asignatura = ? AND unidad = ? ORDER BY fecha_creacion ASC", 
                       [$nombre_asignatura, $unidad_actual]);
if (!$recursos) $recursos = [];

$entregas_alumno = [];
if ($rol === 'alumno') {
    $mis_entregas = $db->query("SELECT recurso_id FROM Entregas WHERE alumno_id = ?", [$id_usuario]);
    if ($mis_entregas) {
        foreach ($mis_entregas as $e) {
            $entregas_alumno[] = $e['recurso_id'];
        }
    }
}

// 🔔 NUEVO: OBTENER MENSAJES SIN LEER DE LOS FOROS
$foros_no_leidos = [];
$res_unread = $db->query("
    SELECT fm.recurso_id, COUNT(fm.id) as no_leidos
    FROM Foro_Mensajes fm
    LEFT JOIN Foro_Lecturas fl ON fm.recurso_id = fl.recurso_id AND fl.usuario_id = ?
    WHERE fm.usuario_id != ? AND (fl.ultima_lectura IS NULL OR fm.fecha > fl.ultima_lectura)
    GROUP BY fm.recurso_id
", [$id_usuario, $id_usuario]);

if ($res_unread) {
    foreach ($res_unread as $row) {
        $foros_no_leidos[$row['recurso_id']] = (int)$row['no_leidos'];
    }
}

$iconos_recursos = [
    'foro' => 'fa-regular fa-comments text-purple-400',
    'pdf' => 'fa-regular fa-file-pdf text-red-400',
    'tarea' => 'fa-solid fa-file-arrow-up text-blue-400',
    'tarea_opcional' => 'fa-solid fa-file-arrow-up text-zinc-400',
    'examen' => 'fa-solid fa-file-contract text-fuchsia-400'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nombre_asignatura); ?> - Aula Virtual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');
        .aula-container { font-family: 'Inter', system-ui, sans-serif; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
        .tabs-container { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 20px; scrollbar-width: thin; scrollbar-color: #3f3f46 transparent; }
        .tab-btn { white-space: nowrap; padding: 10px 20px; border-radius: 8px; font-size: 0.95rem; font-weight: 500; transition: all 0.2s ease; border: 1px solid transparent; text-decoration: none; }
        .tab-btn.active { background-color: #0891b2; color: white; border-color: #06b6d4; box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2); }
        .tab-btn.inactive { background-color: #27272a; color: #a1a1aa; border-color: #3f3f46; }
        .recurso-row { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; border-bottom: 1px solid #27272a; transition: background 0.2s; }
        .recurso-oculto { opacity: 0.5; background-color: rgba(0,0,0,0.2); }
        .btn-profe { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; justify-content: center; align-items: center; font-size: 0.85rem; transition: all 0.2s; text-decoration: none; border: none; cursor: pointer; }
        .btn-edit { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
        .btn-hide { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }
        .btn-show { background: rgba(16, 185, 129, 0.1); color: #34d399; }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .modal-recurso-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 15, 28, 0.95); backdrop-filter: blur(8px); z-index: 2000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; }
        .modal-recurso-overlay.active { display: flex; opacity: 1; }
        .modal-recurso-card { background: #111827; border: 1px solid #3f3f46; border-radius: 20px; padding: 40px; width: 90%; max-width: 500px; position: relative; transform: translateY(20px); transition: transform 0.3s ease; max-height: 90vh; overflow-y: auto;}
        .modal-recurso-overlay.active .modal-recurso-card { transform: translateY(0); }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; color: #a1a1aa; font-size: 0.95rem; }
        .form-input, .form-select { width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #3f3f46; background: #18181b; color: white; outline: none; }
        .modal-recurso-card::-webkit-scrollbar { width: 6px; }
        .modal-recurso-card::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 10px; }
        @keyframes pulse-soft {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .animate-pulse-soft { animation: pulse-soft 2s infinite; }
    </style>
</head>
<body class="text-zinc-200 bg-zinc-950">

    <?php include '../includes/header.php'; ?>

    <div class="aula-container min-h-screen pt-8 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            
            <div class="mb-8">
                <a href="<?php echo $rol === 'profesor' ? 'profesor/index.php' : 'alumno/index.php'; ?>" class="text-cyan-500 hover:text-cyan-400 text-sm mb-4 inline-block no-underline transition-colors">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver a Mi Panel
                </a>
                <div class="flex justify-between items-center flex-wrap gap-4 mt-2">
                    <h1 class="text-3xl font-bold title-font text-white m-0"><?php echo htmlspecialchars($nombre_asignatura); ?></h1>
                    
                    <?php if($rol === 'profesor'): ?>
                        <div class="flex gap-3 items-center">
                            <a href="alumnos_notas.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>" class="bg-zinc-800 hover:bg-zinc-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors border border-zinc-700 no-underline flex items-center gap-2">
                                <i class="fa-solid fa-users text-cyan-400"></i> Alumnos
                            </a>
                            <a href="calificador.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>" class="relative bg-zinc-800 hover:bg-zinc-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors border border-zinc-700 no-underline flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-amber-500"></i> Calificador
                                <?php if ($entregas_pendientes_aula > 0): ?>
                                    <div class="absolute -top-2 -right-2 bg-amber-500 text-zinc-900 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-zinc-900 animate-pulse-soft shadow-[0_0_10px_rgba(245,158,11,0.5)]">
                                        <?php echo $entregas_pendientes_aula; ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <button id="btnAbrirModalAñadir" class="bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-semibold px-5 py-2.5 rounded-xl transition-colors border-none cursor-pointer flex items-center gap-2">
                                <i class="fa-solid fa-plus"></i> Añadir Recurso
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tabs-container">
                <?php foreach($pestañas as $key => $nombre_pestaña): ?>
                    <a href="aula.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>&unidad=<?php echo urlencode($key); ?>" 
                       class="tab-btn <?php echo $unidad_actual === (string)$key ? 'active' : 'inactive'; ?>">
                        <?php echo htmlspecialchars($nombre_pestaña); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-xl overflow-hidden mt-4">
                <div class="px-6 py-4 border-b border-zinc-800 bg-zinc-900/50">
                    <h3 class="text-cyan-400 font-semibold m-0">Contenido de: <?php echo htmlspecialchars($pestañas[$unidad_actual]); ?></h3>
                </div>

                <div class="flex flex-col">
                    <?php foreach($recursos as $recurso): ?>
                        <?php 
                            if ($recurso['visible'] == 0 && $rol === 'alumno') continue; 
                            $clase_oculto = $recurso['visible'] == 0 ? 'recurso-oculto' : '';
                        ?>
                        <div class="recurso-row <?php echo $clase_oculto; ?>">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-zinc-800 flex justify-center items-center text-xl border border-zinc-700/50">
                                    <i class="<?php echo $iconos_recursos[$recurso['tipo']] ?? 'fa-solid fa-file'; ?>"></i>
                                </div>
                                
                                <?php if(in_array($recurso['tipo'], ['tarea', 'tarea_opcional', 'examen'])): ?>
                                    <a href="tarea.php?id=<?php echo $recurso['id']; ?>" class="text-cyan-400 hover:text-cyan-300 font-medium no-underline transition-colors">
                                        <?php echo htmlspecialchars($recurso['titulo']); ?> <i class="fa-solid fa-arrow-right text-xs ml-1 opacity-50"></i>
                                    </a>
                                
                                <?php elseif($recurso['tipo'] === 'foro'): ?>
                                    <?php $num_no_leidos = $foros_no_leidos[$recurso['id']] ?? 0; ?>
                                    <div class="flex items-center gap-3">
                                        <a href="foro.php?id=<?php echo $recurso['id']; ?>" class="text-purple-400 hover:text-purple-300 font-medium no-underline transition-colors flex items-center gap-1.5">
                                            <?php echo htmlspecialchars($recurso['titulo']); ?> <i class="fa-solid fa-comments text-xs opacity-50"></i>
                                        </a>
                                        <?php if ($num_no_leidos > 0): ?>
                                            <span class="bg-purple-500 text-white text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-[0_0_8px_rgba(168,85,247,0.5)] animate-pulse-soft">
                                                <?php echo $num_no_leidos; ?> nuevo<?php echo $num_no_leidos > 1 ? 's' : ''; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                <?php elseif(!empty($recurso['archivo_url'])): ?>
                                    <a href="../<?php echo $recurso['archivo_url']; ?>" target="_blank" class="text-zinc-200 hover:text-cyan-400 font-medium no-underline transition-colors">
                                        <?php echo htmlspecialchars($recurso['titulo']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-zinc-200 font-medium"><?php echo htmlspecialchars($recurso['titulo']); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-3">
                                <?php if($rol === 'alumno'): ?>
                                    <?php if(in_array($recurso['tipo'], ['tarea', 'tarea_opcional', 'examen'])): ?>
                                        <?php if(in_array($recurso['id'], $entregas_alumno)): ?>
                                            <span class="text-xs bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-3 py-1.5 rounded-full font-medium flex items-center gap-1.5">
                                                <i class="fa-solid fa-check"></i> Entregado
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs bg-zinc-800 border border-zinc-700 text-zinc-400 px-3 py-1.5 rounded-full font-medium">
                                                Pendiente
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs bg-zinc-800 px-3 py-1 rounded-full text-zinc-400">Visto</span>
                                    <?php endif; ?>
                                    
                                <?php elseif($rol === 'profesor'): ?>
                                    <button class="btn-profe btn-edit btn-abrir-editar" 
                                            data-id="<?php echo $recurso['id']; ?>" 
                                            data-tipo="<?php echo $recurso['tipo']; ?>" 
                                            data-titulo="<?php echo htmlspecialchars($recurso['titulo']); ?>"
                                            data-fecha="<?php echo $recurso['fecha_limite'] ? date('Y-m-d\TH:i', strtotime($recurso['fecha_limite'])) : ''; ?>"
                                            data-tamano="<?php echo $recurso['tamano_maximo'] ?? '5'; ?>">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="aula.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>&unidad=<?php echo urlencode($unidad_actual); ?>&accion_recurso=toggle_visibilidad&id_recurso=<?php echo $recurso['id']; ?>" class="btn-profe <?php echo $recurso['visible'] ? 'btn-hide' : 'btn-show'; ?>">
                                        <i class="fa-solid <?php echo $recurso['visible'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                    </a>
                                    <a href="aula.php?asignatura=<?php echo urlencode($nombre_asignatura); ?>&unidad=<?php echo urlencode($unidad_actual); ?>&accion_recurso=borrar&id_recurso=<?php echo $recurso['id']; ?>" onclick="return confirm('¿Borrar recurso?')" class="btn-profe btn-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if($rol === 'profesor'): ?>
    
    <div class="modal-recurso-overlay" id="modalAñadir">
        <div class="modal-recurso-card">
            <h2 class="text-xl text-white mb-6">Añadir Recurso</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="nuevo_recurso">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_recurso" id="select_tipo_añadir" class="form-select">
                        <option value="pdf">PDF / Material</option>
                        <option value="foro">Foro</option>
                        <option value="tarea">Práctica Evaluable</option>
                        <option value="tarea_opcional">Práctica Voluntaria</option>
                        <option value="examen">Examen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo_recurso" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Archivo de enunciado / apuntes (Opcional)</label>
                    <input type="file" name="archivo_recurso" class="form-input">
                </div>

                <div id="opciones_extra_añadir" style="display: none; background: rgba(0, 212, 255, 0.05); border: 1px solid rgba(0, 212, 255, 0.2); padding: 20px; border-radius: 12px; margin-top: 25px; margin-bottom: 20px;">
                    <h3 class="text-sm text-cyan-400 font-bold mb-4 uppercase tracking-wider"><i class="fa-solid fa-sliders mr-2"></i>Ajustes de la Entrega</h3>
                    <div class="form-group mb-4">
                        <label class="form-label"><i class="fa-regular fa-calendar-xmark mr-1"></i> Fecha límite de entrega (Opcional)</label>
                        <input type="datetime-local" name="fecha_limite" class="form-input">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label"><i class="fa-solid fa-weight-scale mr-1"></i> Tamaño máximo del archivo</label>
                        <select name="tamano_maximo" class="form-select">
                            <option value="2">2 MB</option>
                            <option value="5" selected>5 MB (Recomendado)</option>
                            <option value="10">10 MB</option>
                            <option value="20">20 MB</option>
                            <option value="50">50 MB</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-cyan-500 py-3 rounded-xl font-bold text-zinc-900 border-none cursor-pointer mt-2 hover:bg-cyan-400 transition-colors">Guardar</button>
                <button type="button" id="btnCerrarAñadir" class="w-full bg-transparent border-none text-zinc-500 mt-2 cursor-pointer hover:text-white transition-colors">Cancelar</button>
            </form>
        </div>
    </div>

    <div class="modal-recurso-overlay" id="modalEditar">
        <div class="modal-recurso-card">
            <h2 class="text-xl text-white mb-6">Editar Recurso</h2>
            <form action="" method="POST">
                <input type="hidden" name="accion" value="editar_recurso">
                <input type="hidden" name="id_recurso_edit" id="id_recurso_edit">
                
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo_recurso_edit" id="tipo_recurso_edit" class="form-select">
                        <option value="pdf">PDF / Material</option>
                        <option value="foro">Foro</option>
                        <option value="tarea">Práctica Evaluable</option>
                        <option value="tarea_opcional">Práctica Voluntaria</option>
                        <option value="examen">Examen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo_recurso_edit" id="titulo_recurso_edit" class="form-input" required>
                </div>

                <div id="opciones_extra_editar" style="display: none; background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); padding: 20px; border-radius: 12px; margin-top: 25px; margin-bottom: 20px;">
                    <h3 class="text-sm text-blue-400 font-bold mb-4 uppercase tracking-wider"><i class="fa-solid fa-sliders mr-2"></i>Ajustes de la Entrega</h3>
                    <div class="form-group mb-4">
                        <label class="form-label"><i class="fa-regular fa-calendar-xmark mr-1"></i> Fecha límite de entrega (Opcional)</label>
                        <input type="datetime-local" name="fecha_limite_edit" id="fecha_limite_edit" class="form-input">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label"><i class="fa-solid fa-weight-scale mr-1"></i> Tamaño máximo del archivo</label>
                        <select name="tamano_maximo_edit" id="tamano_maximo_edit" class="form-select">
                            <option value="2">2 MB</option>
                            <option value="5">5 MB (Recomendado)</option>
                            <option value="10">10 MB</option>
                            <option value="20">20 MB</option>
                            <option value="50">50 MB</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-500 py-3 rounded-xl font-bold text-white border-none cursor-pointer mt-2 hover:bg-blue-400 transition-colors">Actualizar</button>
                <button type="button" id="btnCerrarEditar" class="w-full bg-transparent border-none text-zinc-500 mt-2 cursor-pointer hover:text-white transition-colors">Cancelar</button>
            </form>
        </div>
    </div>
    
    <script>
        const modalAñadir = document.getElementById('modalAñadir');
        const selectTipoAñadir = document.getElementById('select_tipo_añadir');
        const opcionesAñadir = document.getElementById('opciones_extra_añadir');

        function needsExtraOptions(val) {
            return ['tarea', 'tarea_opcional', 'examen'].includes(val);
        }

        document.getElementById('btnAbrirModalAñadir')?.addEventListener('click', () => {
            selectTipoAñadir.value = 'pdf'; 
            opcionesAñadir.style.display = 'none';
            modalAñadir.classList.add('active');
        });
        document.getElementById('btnCerrarAñadir')?.addEventListener('click', () => modalAñadir.classList.remove('active'));
        
        selectTipoAñadir?.addEventListener('change', (e) => {
            if(needsExtraOptions(e.target.value)) {
                opcionesAñadir.style.display = 'block';
            } else {
                opcionesAñadir.style.display = 'none';
            }
        });

        const modalEditar = document.getElementById('modalEditar');
        const selectTipoEditar = document.getElementById('tipo_recurso_edit');
        const opcionesEditar = document.getElementById('opciones_extra_editar');

        document.getElementById('btnCerrarEditar')?.addEventListener('click', () => modalEditar.classList.remove('active'));

        selectTipoEditar?.addEventListener('change', (e) => {
            if(needsExtraOptions(e.target.value)) {
                opcionesEditar.style.display = 'block';
            } else {
                opcionesEditar.style.display = 'none';
            }
        });

        document.querySelectorAll('.btn-abrir-editar').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('id_recurso_edit').value = this.dataset.id;
                document.getElementById('titulo_recurso_edit').value = this.dataset.titulo;
                
                const tipo = this.dataset.tipo;
                selectTipoEditar.value = tipo;
                
                if(needsExtraOptions(tipo)) {
                    opcionesEditar.style.display = 'block';
                    document.getElementById('fecha_limite_edit').value = this.dataset.fecha;
                    document.getElementById('tamano_maximo_edit').value = this.dataset.tamano;
                } else {
                    opcionesEditar.style.display = 'none';
                }

                modalEditar.classList.add('active');
            });
        });
    </script>
    <?php endif; ?>

    <?php include '../includes/footer.php'; ?>
</body>
</html>