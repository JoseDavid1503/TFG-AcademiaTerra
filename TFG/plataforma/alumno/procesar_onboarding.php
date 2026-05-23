<?php
session_start();

// 1. Control de seguridad (Solo alumnos logueados)
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'alumno') {
    header('Location: ../../login.php');
    exit;
}

// 2. Comprobar que venimos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nombre_curso'])) {
    
    require_once '../../config/config.php';
    require_once '../../config/db_pdo.php';
    $db = DB::open();

    $id_alumno = $_SESSION['user_id'];
    $nombre_curso = $_POST['nombre_curso'];

    // 3. Actualizamos la tabla Alumnos usando tu función wrapper update()
    $actualizado = $db->update('Alumnos', [
        'id' => $id_alumno,
        'curso_matriculado' => $nombre_curso
    ]);

    // 4. Redirigimos de vuelta al panel principal
    if ($actualizado) {
        header('Location: index.php?success=matriculado');
        exit;
    } else {
        die("Error al procesar la matrícula. Por favor, contacta con el administrador.");
    }
} else {
    // Si alguien entra directamente a este archivo sin enviar el formulario, lo echamos
    header('Location: index.php');
    exit;
}