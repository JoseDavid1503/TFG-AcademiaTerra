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

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

$primer_dia_mes = mktime(0, 0, 0, $mes, 1, $anio);
$numero_dias = date('t', $primer_dia_mes);
$dia_semana_inicio = date('N', $primer_dia_mes); 

$meses_es = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$params = [$anio, $mes];
if ($rol === 'alumno') {
    $alumno = $db->query("SELECT curso_matriculado FROM Alumnos WHERE id = ?", [$id_usuario]);
    $curso = $alumno[0]['curso_matriculado'] ?? '';
    $sql_eventos = "SELECT r.id, r.titulo, r.tipo, r.fecha_limite, r.asignatura FROM Recursos r JOIN Asignaturas a ON r.asignatura = a.nombre WHERE a.curso = ? AND r.fecha_limite IS NOT NULL AND YEAR(r.fecha_limite) = ? AND MONTH(r.fecha_limite) = ? AND r.visible = 1";
    array_unshift($params, $curso);
} else {
    $profe = $db->query("SELECT curso_asignado FROM Profesores WHERE id = ?", [$id_usuario]);
    $curso = $profe[0]['curso_asignado'] ?? '';
    $sql_eventos = "SELECT r.id, r.titulo, r.tipo, r.fecha_limite, r.asignatura FROM Recursos r JOIN Asignaturas a ON r.asignatura = a.nombre WHERE a.curso = ? AND r.fecha_limite IS NOT NULL AND YEAR(r.fecha_limite) = ? AND MONTH(r.fecha_limite) = ?";
    array_unshift($params, $curso);
}

$res_eventos = $db->query($sql_eventos, $params);
$eventos_por_dia = [];
if ($res_eventos) {
    foreach ($res_eventos as $ev) {
        $dia_ev = (int)date('j', strtotime($ev['fecha_limite']));
        $eventos_por_dia[$dia_ev][] = $ev;
    }
}

$mes_ant = $mes - 1; $anio_ant = $anio;
if ($mes_ant == 0) { $mes_ant = 12; $anio_ant--; }
$mes_sig = $mes + 1; $anio_sig = $anio;
if ($mes_sig == 13) { $mes_sig = 1; $anio_sig++; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario - Academia Terra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .title-font { font-family: 'Space Grotesk', sans-serif; }
        
        /* REJILLA CORREGIDA CON BORDES REALES */
        .calendar-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-top: 1px solid #27272a;
            border-left: 1px solid #27272a;
            border-radius: 12px;
            overflow: hidden;
            background: #09090b;
        }
        
        .day-header {
            background: #18181b;
            color: #71717a;
            padding: 15px;
            text-align: center;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-right: 1px solid #27272a;
            border-bottom: 1px solid #27272a;
        }

        .day-box {
            min-height: 140px;
            padding: 12px;
            border-right: 1px solid #27272a;
            border-bottom: 1px solid #27272a;
            transition: all 0.2s ease;
            position: relative;
        }

        .day-box:hover {
            background: #121214;
        }

        .day-box.today {
            background: rgba(6, 182, 212, 0.03);
        }
        
        .day-box.today::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border: 2px solid #06b6d4;
            pointer-events: none;
            z-index: 10;
        }

        .empty-day {
            background: #050507;
            border-right: 1px solid #27272a;
            border-bottom: 1px solid #27272a;
        }

        .event-link {
            display: block;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 6px;
            text-decoration: none;
            transition: transform 0.1s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-link:hover {
            transform: scale(1.02);
            filter: brightness(1.2);
        }
    </style>
</head>
<body class="bg-zinc-950 text-zinc-200 min-h-screen">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto w-full py-10 px-6">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white title-font m-0">Calendario de Entregas</h1>
                <p class="text-zinc-500 mt-1">Curso actual: <span class="text-cyan-400 font-semibold"><?php echo htmlspecialchars($curso); ?></span></p>
            </div>
            
            <div class="flex items-center gap-2 bg-zinc-900 p-1.5 rounded-2xl border border-zinc-800">
                <a href="?mes=<?php echo $mes_ant; ?>&anio=<?php echo $anio_ant; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-zinc-800 text-zinc-400"><i class="fa-solid fa-chevron-left"></i></a>
                <span class="text-white font-bold min-w-[160px] text-center title-font text-lg">
                    <?php echo $meses_es[$mes] . " " . $anio; ?>
                </span>
                <a href="?mes=<?php echo $mes_sig; ?>&anio=<?php echo $anio_sig; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-zinc-800 text-zinc-400"><i class="fa-solid fa-chevron-right"></i></a>
            </div>
        </div>

        <div class="calendar-container shadow-2xl">
            <div class="day-header">Lunes</div>
            <div class="day-header">Martes</div>
            <div class="day-header">Miércoles</div>
            <div class="day-header">Jueves</div>
            <div class="day-header">Viernes</div>
            <div class="day-header">Sábado</div>
            <div class="day-header">Domingo</div>

            <?php
            for ($i = 1; $i < $dia_semana_inicio; $i++) {
                echo '<div class="empty-day"></div>';
            }

            for ($dia = 1; $dia <= $numero_dias; $dia++) {
                $es_hoy = ($dia == (int)date('j') && $mes == (int)date('n') && $anio == (int)date('Y')) ? 'today' : '';
                echo '<div class="day-box ' . $es_hoy . '">';
                echo '<span class="text-xs font-bold ' . ($es_hoy ? 'text-cyan-400' : 'text-zinc-600') . '">' . sprintf("%02d", $dia) . '</span>';
                
                if (isset($eventos_por_dia[$dia])) {
                    foreach ($eventos_por_dia[$dia] as $ev) {
                        $estilo = ($ev['tipo'] === 'examen') 
                            ? 'bg-red-500/10 text-red-400 border border-red-500/20' 
                            : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20';
                        
                        echo '<a href="tarea.php?id='.$ev['id'].'" class="event-link '.$estilo.'">';
                        echo '<i class="fa-solid fa-circle text-[6px] mr-2"></i>' . htmlspecialchars($ev['titulo']);
                        echo '</a>';
                    }
                }
                echo '</div>';
            }

            $total_celdas = ($dia_semana_inicio - 1) + $numero_dias;
            $pendientes = (7 - ($total_celdas % 7)) % 7;
            for ($i = 0; $i < $pendientes; $i++) {
                echo '<div class="empty-day"></div>';
            }
            ?>
        </div>

        <div class="mt-10 flex justify-center gap-8 bg-zinc-900/40 w-fit mx-auto px-8 py-4 rounded-2xl border border-zinc-800/50">
            <div class="flex items-center gap-3"><span class="w-3 h-3 rounded-full bg-cyan-500 shadow-[0_0_10px_rgba(6,182,212,0.4)]"></span> <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest">Tareas</span></div>
            <div class="flex items-center gap-3"><span class="w-3 h-3 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.4)]"></span> <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest">Exámenes</span></div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>