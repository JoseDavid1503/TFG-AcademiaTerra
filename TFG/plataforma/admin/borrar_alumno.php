<?php
session_start();

// --- 🛡️ GUARDIA DE SEGURIDAD ---
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../config/db_pdo.php';

// Comprobamos si nos han pasado una ID por la URL
if (isset($_GET['id'])) {
    $id_alumno = $_GET['id'];
    
    $db = DB::open();
    
    if ($db) {
        // Ejecutamos la guillotina: Borramos al alumno de la base de datos
        $sql = "DELETE FROM Alumnos WHERE id = ?";
        $db->query($sql, [$id_alumno]);
    }
}

// Ya hemos terminado, devolvemos al jefe a la tabla
header('Location: index.php');
exit;
?>