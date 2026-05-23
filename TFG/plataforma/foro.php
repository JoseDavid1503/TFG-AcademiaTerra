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
$id_usuario = $_SESSION['user_id'];
$id_recurso = (int)($_GET['id'] ?? 0);

// 1. Obtenemos los datos del Foro
$recurso = $db->query("SELECT * FROM Recursos WHERE id = ? AND tipo = 'foro'", [$id_recurso]);
if (empty($recurso)) {
    exit("Foro no encontrado.");
}
$foro = $recurso[0];

// 2. Procesar nuevo mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    if (!empty($mensaje)) {
        $db->query("INSERT INTO Foro_Mensajes (recurso_id, usuario_id, tipo_usuario, mensaje) VALUES (?, ?, ?, ?)", 
                   [$id_recurso, $id_usuario, $rol, $mensaje]);
        header("Location: foro.php?id=" . $id_recurso);
        exit;
    }
}

// 3. REGISTRAR LECTURA: Guardamos que el usuario acaba de leer el foro
$db->query("INSERT INTO Foro_Lecturas (usuario_id, recurso_id, ultima_lectura) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE ultima_lectura = NOW()", [$id_usuario, $id_recurso]);

// 4. Obtener todos los mensajes
$sql_mensajes = "
    SELECT fm.*, a.nombre, a.apellidos 
    FROM Foro_Mensajes fm 
    JOIN Alumnos a ON fm.usuario_id = a.id 
    WHERE fm.tipo_usuario = 'alumno' AND fm.recurso_id = ?
    
    UNION
    
    SELECT fm.*, p.nombre, p.apellidos 
    FROM Foro_Mensajes fm 
    JOIN Profesores p ON fm.usuario_id = p.id 
    WHERE fm.tipo_usuario = 'profesor' AND fm.recurso_id = ?
    
    ORDER BY fecha ASC
";
$mensajes = $db->query($sql_mensajes, [$id_recurso, $id_recurso]);
if (!$mensajes) $mensajes = [];

function getIniciales($nombre, $apellidos) {
    return strtoupper(substr($nombre, 0, 1) . substr($apellidos, 0, 1));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro: <?php echo htmlspecialchars($foro['titulo']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');
        body { font-family: 'Inter', system-ui, sans-serif; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
        .chat-container::-webkit-scrollbar { width: 6px; }
        .chat-container::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 10px; }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen flex flex-col">
    <?php include '../includes/header.php'; ?>

    <div class="flex-grow max-w-5xl mx-auto w-full py-10 px-6 flex flex-col">
        <a href="aula.php?asignatura=<?php echo urlencode($foro['asignatura']); ?>&unidad=<?php echo urlencode($foro['unidad']); ?>" class="text-cyan-500 hover:text-cyan-400 no-underline mb-6 inline-block transition-colors">
            <i class="fa-solid fa-arrow-left mr-2"></i> Volver al aula
        </a>

        <div class="flex items-center gap-4 mb-6 border-b border-zinc-800 pb-6">
            <div class="w-14 h-14 rounded-xl bg-purple-500/10 flex justify-center items-center text-3xl border border-purple-500/30 text-purple-400 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white m-0 title-font"><?php echo htmlspecialchars($foro['titulo']); ?></h1>
                <p class="text-zinc-400 m-0 text-sm mt-1">Foro de Discusión • <?php echo htmlspecialchars($foro['asignatura']); ?></p>
            </div>
        </div>

        <?php if(!empty($foro['archivo_url'])): ?>
        <div class="mb-6 bg-zinc-900 border border-zinc-800 rounded-2xl p-5 shadow-lg">
            <h3 class="text-sm text-zinc-400 uppercase tracking-wider mb-3 font-semibold">Archivo adjunto al foro</h3>
            <a href="../<?php echo $foro['archivo_url']; ?>" target="_blank" class="flex items-center gap-3 bg-zinc-800 hover:bg-zinc-700 p-4 rounded-xl w-max transition-colors no-underline text-white">
                <i class="fa-regular fa-file-pdf text-red-400 text-xl shrink-0"></i>
                <span><?php echo htmlspecialchars(basename($foro['archivo_url'])); ?></span>
            </a>
        </div>
        <?php endif; ?>

        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl shadow-xl flex flex-col flex-grow overflow-hidden mb-6 h-[500px]">
            <div class="flex-grow overflow-y-auto p-6 space-y-6 chat-container bg-zinc-950/30">
                <?php if(empty($mensajes)): ?>
                    <div class="h-full flex flex-col items-center justify-center text-zinc-500">
                        <i class="fa-regular fa-comments text-5xl mb-4 opacity-50"></i>
                        <p>Aún no hay mensajes. ¡Sé el primero en participar!</p>
                    </div>
                <?php else: ?>
                    <?php foreach($mensajes as $msg): 
                        $es_mio = ($msg['usuario_id'] == $id_usuario && $msg['tipo_usuario'] == $rol);
                        $es_profe = ($msg['tipo_usuario'] == 'profesor');
                        $iniciales = getIniciales($msg['nombre'], $msg['apellidos']);
                        
                        $bg_color = $es_mio ? 'bg-cyan-900/40 border-cyan-800' : ($es_profe ? 'bg-purple-900/30 border-purple-800/50' : 'bg-zinc-800 border-zinc-700');
                        $avatar_color = $es_profe ? 'bg-purple-500 text-white' : 'bg-zinc-700 text-zinc-300';
                    ?>
                        <div class="flex gap-4 <?php echo $es_mio ? 'flex-row-reverse' : ''; ?>">
                            <div class="w-10 h-10 rounded-full <?php echo $avatar_color; ?> flex items-center justify-center font-bold text-sm shrink-0 shadow-lg">
                                <?php echo $iniciales; ?>
                            </div>
                            <div class="max-w-[80%] <?php echo $es_mio ? 'items-end' : 'items-start'; ?> flex flex-col">
                                <div class="flex items-baseline gap-2 mb-1 <?php echo $es_mio ? 'flex-row-reverse' : ''; ?>">
                                    <span class="text-sm font-bold text-zinc-300">
                                        <?php echo htmlspecialchars($msg['nombre'] . ' ' . $msg['apellidos']); ?>
                                        <?php if($es_profe): ?>
                                            <i class="fa-solid fa-shield-halved text-purple-400 ml-1 text-xs" title="Profesor"></i>
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-xs text-zinc-500"><?php echo date('d/m/Y H:i', strtotime($msg['fecha'])); ?></span>
                                </div>
                                <div class="p-4 rounded-2xl border <?php echo $bg_color; ?> text-zinc-200 whitespace-pre-wrap leading-relaxed shadow-sm">
                                    <?php echo htmlspecialchars($msg['mensaje']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="p-4 bg-zinc-900 border-t border-zinc-800">
                <form action="" method="POST" class="flex gap-3">
                    <textarea name="mensaje" rows="1" placeholder="Escribe tu mensaje aquí..." class="flex-grow bg-zinc-800 border border-zinc-700 text-white p-3 rounded-xl outline-none focus:border-cyan-500 transition-colors resize-none" required></textarea>
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-zinc-900 font-bold px-6 rounded-xl transition-colors shadow-lg shadow-cyan-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        const chatContainer = document.querySelector('.chat-container');
        if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
        
        const textarea = document.querySelector('textarea');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight < 150 ? this.scrollHeight : 150) + 'px';
        });
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>