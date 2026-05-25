<?php
session_start();

// 1. Seguridad: Solo admin puede exportar
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    exit('Acceso denegado');
}

// Rutas de conexión (estamos dentro de admin/, así que hay que subir 2 niveles)
require_once '../../config/config.php'; 
require_once '../../config/db_pdo.php';

$db = DB::open();

// 2. Detectamos qué queremos exportar mediante la URL (por defecto alumnos)
$tipo_exportacion = $_GET['tipo'] ?? 'alumnos';

if ($tipo_exportacion === 'profesores') {
    // Consulta para Profesores (Incluimos todas las columnas relevantes de tu tabla)
    $sql = "SELECT id, nombre, apellidos, dni, email, telefono, especialidad, curso_asignado FROM Profesores ORDER BY id DESC";
    $filename = "profesores_academia_terra_" . date('Y-m-d') . ".csv";
    $columnas = ['ID', 'Nombre', 'Apellidos', 'DNI', 'Email', 'Telefono', 'Especialidad', 'Curso Asignado'];
} else {
    // Consulta para Alumnos
    $sql = "SELECT id, nombre, apellidos, dni, email, telefono, curso_matriculado FROM Alumnos ORDER BY id DESC";
    $filename = "alumnos_academia_terra_" . date('Y-m-d') . ".csv";
    $columnas = ['ID', 'Nombre', 'Apellidos', 'DNI', 'Email', 'Telefono', 'Curso Matriculado'];
}

$datos = $db->query($sql);

if (empty($datos) || !is_array($datos)) {
    exit('No hay datos para exportar. Asegúrate de que hay usuarios registrados.');
}

// 3. Configurar cabeceras de Excel
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

// BOM para UTF-8 (Para que las tildes y las ñ se vean bien en Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 4. Escribir los títulos de las columnas
fputcsv($output, $columnas);

// 5. Escribir los datos línea a línea
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
            $fila['especialidad'] ?? '---', 
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
