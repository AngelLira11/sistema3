<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/profile_check.php';
require_once __DIR__ . '/../auth/csrf.php';

// Verificar que el usuario esté autenticado
if (empty($_SESSION['alumno_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/index.php');
    exit;
}

csrf_validate();

$id                = $_SESSION['alumno_id'];
$nombre            = trim($_POST['nombre'] ?? '');
$no_control        = trim($_POST['no_control'] ?? '');
$carrera           = trim($_POST['carrera'] ?? '');
$opcion_titulacion = trim($_POST['opcion_titulacion'] ?? '');
$celular           = trim($_POST['celular'] ?? '');
$fecha_egreso      = trim($_POST['fecha_egreso'] ?? '');
$graduacion        = trim($_POST['graduacion'] ?? '');

// Validar campos obligatorios (email NO se valida aquí, ya existe del registro)
if (empty($nombre) || empty($no_control) || empty($carrera) || empty($opcion_titulacion)
    || empty($celular) || empty($fecha_egreso) || empty($graduacion)) {
    header('Location: ../../public/complete_profile.php?error=campos');
    exit;
}

// Validar teléfono
if (!preg_match('/^[0-9]{10,15}$/', $celular)) {
    header('Location: ../../public/complete_profile.php?error=formato');
    exit;
}

// Validar graduacion
if (!in_array($graduacion, ['1', '2'])) {
    header('Location: ../../public/complete_profile.php?error=formato');
    exit;
}

// Validar y procesar fecha
$anio_egreso = (int)date('Y', strtotime($fecha_egreso));
if ($anio_egreso < 2020 || $anio_egreso > 2099) {
    header('Location: ../../public/complete_profile.php?error=formato');
    exit;
}

try {
    $pdo = getConexion();

    // Obtener el alumno actual
    $current_alumno = getAlumnoById($pdo, $id);
    if (!$current_alumno) {
        session_destroy();
        header('Location: ../../public/index.php');
        exit;
    }

    // Verificar duplicados SOLO del no_control (email no se cambia)
    $check = $pdo->prepare("
        SELECT id FROM alumnos 
        WHERE no_control = ? AND id != ? 
        LIMIT 1
    ");
    $check->execute([$no_control, $id]);
    if ($check->fetch()) {
        header('Location: ../../public/complete_profile.php?error=duplicado');
        exit;
    }

    // Actualizar el perfil del alumno (SIN cambiar email)
    $stmt = $pdo->prepare("
        UPDATE alumnos 
        SET nombre=?, no_control=?, carrera=?, opcion_titulacion=?,
            celular=?, fecha_egreso=?, graduacion=?, anio_egreso=?
        WHERE id=?
    ");
    $stmt->execute([
        $nombre, $no_control, $carrera, $opcion_titulacion,
        $celular, $fecha_egreso, $graduacion, $anio_egreso, $id
    ]);

    // Verificar si el perfil ahora está completo
    $updated_alumno = getAlumnoById($pdo, $id);
    $profile_check = isProfileIncomplete($updated_alumno);

    if ($profile_check['incomplete']) {
        // El perfil sigue siendo incompleto (esto no debería suceder si validamos bien)
        header('Location: ../../public/complete_profile.php?error=campos');
        exit;
    }

    // Redirigir al dashboard
    header('Location: ../../public/dashboard.php?profile_completed=1');
    exit;

} catch (Exception $e) {
    die($e->getMessage()); // Borrar en producción
}
?>
