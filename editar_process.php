<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['alumno_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id                = $_SESSION['alumno_id'];
$nombre            = trim($_POST['nombre']            ?? '');
$no_control        = trim($_POST['no_control']        ?? '');
$carrera           = trim($_POST['carrera']           ?? '');
$opcion_titulacion = trim($_POST['opcion_titulacion'] ?? '');
$email             = trim($_POST['email']             ?? '');
$celular           = trim($_POST['celular']           ?? '');
$fecha_egreso      = trim($_POST['fecha_egreso']      ?? '');
$graduacion        = trim($_POST['graduacion']        ?? '');
$password          = $_POST['password']  ?? '';
$password2         = $_POST['password2'] ?? '';

// Validar campos obligatorios
if (empty($nombre) || empty($no_control) || empty($carrera) || empty($opcion_titulacion)
    || empty($email) || empty($celular) || empty($fecha_egreso) || empty($graduacion)) {
    header('Location: editar_datos.php?error=campos');
    exit;
}

// Validar graduacion
if (!in_array($graduacion, ['1', '2'])) {
    header('Location: editar_datos.php?error=campos');
    exit;
}

// Validar contraseña solo si se quiere cambiar
if (!empty($password)) {
    if ($password !== $password2) {
        header('Location: editar_datos.php?error=password');
        exit;
    }
}

$anio_egreso = (int)date('Y', strtotime($fecha_egreso));

try {
    $pdo = getConexion();

    // Verificar duplicados excluyendo al alumno actual
    $check = $pdo->prepare("
        SELECT id FROM alumnos 
        WHERE (email = ? OR no_control = ?) AND id != ? 
        LIMIT 1
    ");
    $check->execute([$email, $no_control, $id]);
    if ($check->fetch()) {
        header('Location: editar_datos.php?error=duplicado');
        exit;
    }

    // Actualizar con o sin contraseña
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            UPDATE alumnos 
            SET nombre=?, no_control=?, carrera=?, opcion_titulacion=?,
                email=?, celular=?, fecha_egreso=?, graduacion=?, anio_egreso=?, password=?
            WHERE id=?
        ");
        $stmt->execute([$nombre, $no_control, $carrera, $opcion_titulacion,
                        $email, $celular, $fecha_egreso, $graduacion, $anio_egreso, $hash, $id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE alumnos 
            SET nombre=?, no_control=?, carrera=?, opcion_titulacion=?,
                email=?, celular=?, fecha_egreso=?, graduacion=?, anio_egreso=?
            WHERE id=?
        ");
        $stmt->execute([$nombre, $no_control, $carrera, $opcion_titulacion,
                        $email, $celular, $fecha_egreso, $graduacion, $anio_egreso, $id]);
    }

    header('Location: dashboard.php?ok=1');
    exit;

} catch (Exception $e) {
    die($e->getMessage()); // Borrar en producción
}
?>