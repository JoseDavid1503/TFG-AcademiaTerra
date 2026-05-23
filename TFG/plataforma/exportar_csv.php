<?php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'profesor') exit;

require_once '../config/config.php';
require_once '../config/db_pdo.php';
$db = DB::open();

$nombre_asig = $_GET['asignatura'] ?? 'Notas';

// Cabeceras para forzar la descarga del archivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=Notas_'.str_replace(' ', '_', $nombre_asig).'.csv');

$output = fopen('php://output', 'w');
// Escribir la línea de encabezado del CSV
fputcsv($output, ['ID Alumno', 'Apellidos', 'Nombre', 'Media Actividades', 'Media Examenes']);

// Obtener datos (Misma lógica que la tabla visual)
$asig_info = $db->query("SELECT curso FROM Asignaturas WHERE nombre = ?", [$nombre_asig]);
$curso = $asig_info[0]['curso'] ?? null;
$alumnos = $db->query("SELECT id, nombre, apellidos FROM Alumnos WHERE curso_matriculado = ? ORDER BY apellidos ASC", [$curso]);

$entregas_raw = $db->query("SELECT e.alumno_id, e.nota, r.tipo FROM Entregas e JOIN Recursos r ON e.recurso_id = r.id WHERE r.asignatura = ?", [$nombre_asig]);
$data = [];
foreach($entregas_raw as $er) { $data[$er['alumno_id']][$er['tipo']][] = $er['nota']; }

foreach($alumnos as $al) {
    $acts = array_merge($data[$al['id']]['tarea'] ?? [], $data[$al['id']]['tarea_opcional'] ?? []);
    $exams = $data[$al['id']]['examen'] ?? [];
    
    $m_acts = count($acts) > 0 ? round(array_sum($acts) / count($acts), 2) : '0';
    $m_exams = count($exams) > 0 ? round(array_sum($exams) / count($exams), 2) : '0';

    fputcsv($output, [$al['id'], $al['apellidos'], $al['nombre'], $m_acts, $m_exams]);
}
fclose($output);