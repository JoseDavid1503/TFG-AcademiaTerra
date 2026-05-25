<?php
session_start();

// 1. 🛡️ Seguridad: Solo admin puede exportar
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    exit('Acceso denegado');
}

require_once '../../config/config.php'; 
require_once '../../config/db_pdo.php';

$db = DB::open();

// 2. Determinar qué datos exportar mediante parámetro GET (por defecto alumnos)
$tipo_exportacion = $_GET['tipo'] ?? 'alumnos';

if ($tipo_exportacion === 'profesores') {
    $sql = "SELECT id, nombre, apellidos, dni, email, telefono, especialidad, curso_asignado FROM Profesores ORDER BY id DESC";
    $filename = "profesores_academia_terra_" . date('Y-m-d') . ".csv";
    $columnas = ['ID', 'Nombre', 'Apellidos', 'DNI', 'Email', 'Telefono', 'Especialidad', 'Curso Asignado'];
} else {
    // Por defecto, exporta alumnos
    $sql = "SELECT id, nombre, apellidos, dni, email, telefono, curso_matriculado FROM Alumnos ORDER BY id DESC";
    $filename = "alumnos_academia_terra_" . date('Y-m-d') . ".csv";
    $columnas = ['ID', 'Nombre', 'Apellidos', 'DNI', 'Email', 'Telefono', 'Curso Matriculado'];
}

/** @var array $datos */
$datos = $db->query($sql);

if (empty($datos) || !is_array($datos)) {
    exit('No hay datos para exportar');
}

// 3. Configurar cabeceras para la descarga del archivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 4. Crear el puntero de archivo para la salida (output)
$output = fopen('php://output', 'w');

// Solución para que Excel reconozca los acentos (BOM UTF-8)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 5. Definir los títulos de las columnas
fputcsv($output, $columnas);

// 6. Volcar los datos al CSV
foreach ($datos as $fila) {
    if ($tipo_exportacion === 'profesores') {
        $curso = str_replace('_', ' ', $fila['curso_asignado'] ?? 'Sin asignar');
        fputcsv($output, [
            $fila['id'],
            $fila['nombre'],
            $fila['apellidos'],
            $fila['dni'],
            $fila['email'],
            $fila['telefono'],
            $fila['especialidad'],
            $curso
        ]);
    } else {
        $curso = str_replace('_', ' ', $fila['curso_matriculado'] ?? 'Sin matricular');
        fputcsv($output, [
            $fila['id'],
            $fila['nombre'],
            $fila['apellidos'],
            $fila['dni'],
            $fila['email'],
            $fila['telefono'],
            $curso
        ]);
    }
}

fclose($output);
exit;
