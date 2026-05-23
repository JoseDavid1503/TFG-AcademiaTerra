<?php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'profesor') {
    header('Location: ../login.php'); exit;
}
require_once '../config/config.php';
require_once '../config/db_pdo.php';
$db = DB::open();

$nombre_asig = $_GET['asignatura'] ?? '';

// 1. Obtener curso de la asignatura
$asig_info = $db->query("SELECT curso FROM Asignaturas WHERE nombre = ?", [$nombre_asig]);
$curso = $asig_info[0]['curso'] ?? null;

// 2. Obtener alumnos del curso
$alumnos = $db->query("SELECT id, nombre, apellidos FROM Alumnos WHERE curso_matriculado = ? ORDER BY apellidos ASC", [$curso]);
if (!$alumnos) $alumnos = [];

// 3. Obtener todas las entregas calificadas de esta asignatura para los desgloses
$entregas = $db->query("
    SELECT e.*, r.titulo, r.tipo 
    FROM Entregas e 
    JOIN Recursos r ON e.recurso_id = r.id 
    WHERE r.asignatura = ? AND e.nota IS NOT NULL AND e.nota != ''
", [$nombre_asig]);

$desglose = [];
foreach ($entregas as $en) {
    $desglose[$en['alumno_id']][$en['tipo']][] = $en;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas Alumnos - <?php echo htmlspecialchars($nombre_asig); ?></title>
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

    <div class="flex-grow max-w-6xl mx-auto w-full py-10 px-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="aula.php?asignatura=<?php echo urlencode($nombre_asig); ?>" class="text-cyan-500 hover:text-cyan-400 transition-colors no-underline text-sm mb-2 inline-block"><i class="fa-solid fa-arrow-left mr-2"></i>Volver al aula</a>
                <h1 class="text-3xl font-bold text-white title-font m-0">Seguimiento de Alumnos</h1>
                <p class="text-zinc-500 m-0 mt-1"><?php echo htmlspecialchars($nombre_asig); ?> (<?php echo htmlspecialchars($curso); ?>)</p>
            </div>
            <a href="exportar_csv.php?asignatura=<?php echo urlencode($nombre_asig); ?>" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-emerald-900/20 no-underline flex items-center">
                <i class="fa-solid fa-file-csv mr-2"></i> Exportar a CSV
            </a>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden shadow-2xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-800/50 border-b border-zinc-800 text-zinc-400 uppercase text-xs tracking-widest">
                        <th class="p-6">Estudiante</th>
                        <th class="p-6 text-center">Media Actividades</th>
                        <th class="p-6 text-center">Media Exámenes</th>
                        <th class="p-6 text-right">Estado Global</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php foreach($alumnos as $al): 
                        $acts = $desglose[$al['id']]['tarea'] ?? [];
                        $acts_op = $desglose[$al['id']]['tarea_opcional'] ?? [];
                        $total_acts = array_merge($acts, $acts_op);
                        
                        $exams = $desglose[$al['id']]['examen'] ?? [];

                        $m_acts = count($total_acts) > 0 ? round(array_sum(array_column($total_acts, 'nota')) / count($total_acts), 2) : '-';
                        $m_exams = count($exams) > 0 ? round(array_sum(array_column($exams, 'nota')) / count($exams), 2) : '-';
                    ?>
                    <tr class="hover:bg-zinc-800/30 transition-colors">
                        <td class="p-6">
                            <div class="font-bold text-white"><?php echo htmlspecialchars($al['apellidos'].", ".$al['nombre']); ?></div>
                            <div class="text-xs text-zinc-500 mt-1"><i class="fa-solid fa-id-card mr-1"></i> ID Alumno: #<?php echo $al['id']; ?></div>
                        </td>
                        <td class="p-6 text-center">
                            <button onclick="verDetalle('Actividades: <?php echo htmlspecialchars($al['nombre']); ?>', <?php echo htmlspecialchars(json_encode($total_acts)); ?>)" class="bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 px-5 py-2 rounded-lg font-bold hover:bg-cyan-500 hover:text-zinc-900 transition-all cursor-pointer">
                                <?php echo $m_acts; ?>
                            </button>
                        </td>
                        <td class="p-6 text-center">
                            <button onclick="verDetalle('Exámenes: <?php echo htmlspecialchars($al['nombre']); ?>', <?php echo htmlspecialchars(json_encode($exams)); ?>)" class="bg-purple-500/10 text-purple-400 border border-purple-500/20 px-5 py-2 rounded-lg font-bold hover:bg-purple-500 hover:text-zinc-900 transition-all cursor-pointer">
                                <?php echo $m_exams; ?>
                            </button>
                        </td>
                        <td class="p-6 text-right">
                            <?php if($m_acts !== '-' && $m_acts < 5): ?>
                                <span class="text-red-400 text-xs font-bold bg-red-400/10 border border-red-400/20 px-3 py-1.5 rounded-full">Riesgo</span>
                            <?php elseif($m_acts !== '-'): ?>
                                <span class="text-emerald-400 text-xs font-bold bg-emerald-400/10 border border-emerald-400/20 px-3 py-1.5 rounded-full">Progresando</span>
                            <?php else: ?>
                                <span class="text-zinc-500 text-xs bg-zinc-800 border border-zinc-700 px-3 py-1.5 rounded-full">Sin datos</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalDetalle" class="fixed inset-0 bg-zinc-950/90 backdrop-blur-sm z-50 hidden flex items-center justify-center p-6 opacity-0 transition-opacity duration-300">
        <div class="bg-zinc-900 border border-zinc-700 w-full max-w-lg rounded-3xl p-8 shadow-2xl transform scale-95 transition-transform duration-300" id="modalDetalleCard">
            <div class="flex justify-between items-center mb-6 border-b border-zinc-800 pb-4">
                <h2 id="modalTitulo" class="text-xl font-bold text-white m-0">Detalle</h2>
                <button onclick="cerrarModal()" class="text-zinc-500 hover:text-white text-2xl cursor-pointer bg-transparent border-none">&times;</button>
            </div>
            <div id="modalCuerpo" class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #3f3f46; border-radius: 10px; }
    </style>

    <script>
        const modalDetalle = document.getElementById('modalDetalle');
        const modalDetalleCard = document.getElementById('modalDetalleCard');

        function verDetalle(titulo, datos) {
            document.getElementById('modalTitulo').innerText = titulo;
            const cuerpo = document.getElementById('modalCuerpo');
            cuerpo.innerHTML = '';

            if(datos.length === 0) {
                cuerpo.innerHTML = '<p class="text-zinc-500 text-center py-8"><i class="fa-solid fa-inbox text-3xl mb-3 block opacity-50"></i>No hay entregas calificadas.</p>';
            } else {
                datos.forEach(d => {
                    cuerpo.innerHTML += `
                        <div class="flex justify-between items-center bg-zinc-800/50 p-4 rounded-xl border border-zinc-700 hover:bg-zinc-800 transition-colors">
                            <span class="text-zinc-300 font-medium text-sm"><i class="fa-solid fa-file-lines text-zinc-500 mr-2"></i>${d.titulo}</span>
                            <span class="text-white font-bold bg-zinc-700 px-3 py-1 rounded-lg border border-zinc-600">${d.nota}</span>
                        </div>
                    `;
                });
            }
            
            modalDetalle.classList.remove('hidden');
            setTimeout(() => {
                modalDetalle.classList.add('opacity-100');
                modalDetalleCard.classList.remove('scale-95');
                modalDetalleCard.classList.add('scale-100');
            }, 10);
        }

        function cerrarModal() { 
            modalDetalle.classList.remove('opacity-100');
            modalDetalleCard.classList.remove('scale-100');
            modalDetalleCard.classList.add('scale-95');
            setTimeout(() => {
                modalDetalle.classList.add('hidden');
            }, 300);
        }
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>