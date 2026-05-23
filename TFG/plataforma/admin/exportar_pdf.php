<?php
session_start();

// Cargar la librería Dompdf que acabas de instalar
require_once '../../vendor/autoload.php'; 
require_once '../../config/config.php';
require_once '../../config/db_pdo.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Seguridad: Solo el admin puede generar este reporte global
if (empty($_SESSION['user_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    exit("Acceso denegado.");
}

$db = DB::open();
$view = $_GET['view'] ?? 'alumnos';
$curso = $_GET['curso'] ?? '';

$table = ($view === 'profesores') ? 'Profesores' : 'Alumnos';
$curso_field = ($view === 'profesores') ? 'curso_asignado' : 'curso_matriculado';

// Preparar la consulta
$where = "";
$params = [];
if (!empty($curso)) {
    $where = "WHERE $curso_field = ?";
    $params[] = $curso;
}

$sql = "SELECT nombre, apellidos, dni, email, telefono, $curso_field as curso FROM $table $where ORDER BY apellidos ASC";
$usuarios = $db->query($sql, $params);

// Título dinámico
$titulo = ($view === 'profesores') ? 'Listado Oficial de Profesores' : 'Listado Oficial de Alumnos';
if (!empty($curso)) {
    $titulo .= ' - ' . str_replace('_', ' ', $curso);
}

// Configurar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// DISEÑO DEL PDF (HTML + CSS)
$html = '
<html>
<head>
    <style>
        body { font-family: "Helvetica", Arial, sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #06b6d4; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #06b6d4; margin-bottom: 5px; }
        h2 { color: #111; font-size: 18px; margin-top: 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 20px; }
        th { background-color: #f4f4f5; color: #3f3f46; text-transform: uppercase; padding: 12px 10px; border: 1px solid #d4d4d8; text-align: left; }
        td { padding: 10px; border: 1px solid #d4d4d8; }
        tr:nth-child(even) { background-color: #fafafa; }
        .footer { position: fixed; bottom: -20px; width: 100%; text-align: center; font-size: 10px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">ACADEMIA TERRA</div>
        <h2>' . htmlspecialchars($titulo) . '</h2>
        <p style="font-size: 12px; color: #71717a; margin:0;">Generado el ' . date('d/m/Y') . ' a las ' . date('H:i') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Apellidos y Nombre</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Curso</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($usuarios)) {
    foreach ($usuarios as $u) {
        $html .= '<tr>
                    <td><strong>' . htmlspecialchars($u['apellidos']) . '</strong>, ' . htmlspecialchars($u['nombre']) . '</td>
                    <td>' . htmlspecialchars($u['dni']) . '</td>
                    <td>' . htmlspecialchars($u['email']) . '</td>
                    <td>' . htmlspecialchars($u['telefono']) . '</td>
                    <td>' . htmlspecialchars(str_replace('_', ' ', $u['curso'] ?? '---')) . '</td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center; padding: 20px;">No hay registros para este filtro.</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        Documento oficial de uso interno generado por el sistema de gestión de Academia Terra.
    </div>
</body>
</html>';

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Enviar al navegador (Attachment => false hace que se abra en una pestaña nueva en vez de forzar descarga)
$dompdf->stream("Reporte_" . ucfirst($view) . "_" . date('Ymd') . ".pdf", ["Attachment" => false]);