<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit;
}

// Recoger y limpiar datos
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

// Validar campos vacíos
if (empty($nombre) || empty($no_control) || empty($carrera) || empty($opcion_titulacion)
    || empty($email) || empty($celular) || empty($fecha_egreso) || empty($graduacion)
    || empty($password) || empty($password2)) {
    header('Location: registro.php?error=campos');
    exit;
}

// Validar contraseñas iguales
if ($password !== $password2) {
    header('Location: registro.php?error=password');
    exit;
}

// Extraer el año de la fecha de egreso (ej: 2026-05-15 => 2026)
$anio_egreso = (int)date('Y', strtotime($fecha_egreso));
if ($anio_egreso < 2020 || $anio_egreso > 2099) {
    header('Location: registro.php?error=campos');
    exit;
}

try {
    $pdo = getConexion();

    // Verificar duplicados
    $check = $pdo->prepare("SELECT id FROM alumnos WHERE email = ? OR no_control = ? LIMIT 1");
    $check->execute([$email, $no_control]);
    if ($check->fetch()) {
        header('Location: registro.php?error=duplicado');
        exit;
    }

    // Insertar alumno
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO alumnos (nombre, no_control, carrera, opcion_titulacion, email, celular, fecha_egreso, graduacion, anio_egreso, password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $no_control, $carrera, $opcion_titulacion, $email, $celular, $fecha_egreso, $graduacion, $anio_egreso, $hash]);

    header('Location: index.php?registro=ok');
    exit;

} catch (Exception $e) {
    header('Location: registro.php?error=campos');
    exit;
}
?>
