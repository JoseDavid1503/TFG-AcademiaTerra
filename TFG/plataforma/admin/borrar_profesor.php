<?php
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../config/db_pdo.php';
$db = DB::open();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Al borrar al profesor, gracias al diseño de la BD, no debería haber problemas 
    // a menos que quieras conservar sus datos por histórico.
    $db->query("DELETE FROM Profesores WHERE id = ?", [$id]);
}

header('Location: index.php?view=profesores');
exit;