<?php
session_start();

// 1. 🛡️ Seguridad: Solo admin puede exportar
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    exit('Acceso denegado');
}

// ESTA ES LA LÍNEA QUE FALTABA:
require_once '../../config/config.php'; 
require_once '../../config/db_pdo.php';

$db = DB::open();

// 2. Obtener los datos de los alumnos
$sql = "SELECT id, nombre, apellidos, dni, email, telefono, curso_matriculado FROM Alumnos ORDER BY id DESC";
/** @var array $alumnos */
$alumnos = $db->query($sql);

if (empty($alumnos) || !is_array($alumnos)) {
    exit('No hay datos para exportar');
}

// 3. Configurar cabeceras para la descarga del archivo
$filename = "alumnos_academia_terra_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 4. Crear el puntero de archivo para la salida (output)
$output = fopen('php://output', 'w');

// Solución para que Excel reconozca los acentos (BOM UTF-8)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 5. Definir los títulos de las columnas
fputcsv($output, ['ID', 'Nombre', 'Apellidos', 'DNI', 'Email', 'Telefono', 'Curso']);

// 6. Volcar los datos al CSV
foreach ($alumnos as $alumno) {
    // Limpiamos los textos para el Excel
    $curso = str_replace('_', ' ', $alumno['curso_matriculado'] ?? 'Sin matricular');
    
    fputcsv($output, [
        $alumno['id'],
        $alumno['nombre'],
        $alumno['apellidos'],
        $alumno['dni'],
        $alumno['email'],
        $alumno['telefono'],
        $curso
    ]);
}

fclose($output);
exit;