<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$pdo = getConexion();

$buscar  = $_GET['buscar']            ?? '';
$carrera = $_GET['carrera']           ?? '';
$opcion  = $_GET['opcion_titulacion'] ?? '';
$anio_f  = $_GET['anio']             ?? '';
$grad_f  = $_GET['graduacion']        ?? '';

$sql    = "SELECT anio_egreso, graduacion, no_control, nombre, carrera, opcion_titulacion, email, celular, fecha_egreso FROM alumnos WHERE 1=1";
$params = [];

if (!empty($buscar)) {
    $sql .= " AND (nombre LIKE ? OR no_control LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
}
if (!empty($carrera)) {
    $sql .= " AND carrera = ?";
    $params[] = $carrera;
}
if (!empty($opcion)) {
    $sql .= " AND opcion_titulacion = ?";
    $params[] = $opcion;
}
if (!empty($anio_f)) {
    $sql .= " AND anio_egreso = ?";
    $params[] = $anio_f;
}
if (!empty($grad_f)) {
    $sql .= " AND graduacion = ?";
    $params[] = $grad_f;
}

$sql .= " ORDER BY anio_egreso DESC, graduacion ASC, nombre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="alumnos_titulacion_' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($out, ['Año Egreso', 'Graduación', 'No. Control', 'Nombre', 'Carrera', 'Opción Titulación', 'Email', 'Celular', 'Fecha Egreso']);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['anio_egreso'],
        $row['graduacion'] === '1' ? 'Graduación 1 (Mar-Abr)' : 'Graduación 2 (Nov-Dic)',
        $row['no_control'],
        $row['nombre'],
        $row['carrera'],
        $row['opcion_titulacion'],
        $row['email'],
        $row['celular'],
        $row['fecha_egreso'],
    ]);
}

fclose($out);
exit;
?>