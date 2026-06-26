<?php
session_start();
require_once 'config.php';
require_once 'profile_check.php';

$email_user = trim($_POST['email'] ?? '');
$password   = trim($_POST['password'] ?? '');

$pdo = getConexion();

// Intentar login como alumno
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE email = ? LIMIT 1");
$stmt->execute([$email_user]);
$alumno = $stmt->fetch();

if ($alumno && password_verify($password, $alumno['password'])) {
    $_SESSION['alumno_id'] = $alumno['id'];
    $_SESSION['rol'] = 'alumno';
    
    // Verificar si el perfil está completo
    $profile_check = isProfileIncomplete($alumno);
    if ($profile_check['incomplete']) {
        // Redirigir a completar perfil
        header('Location: complete_profile.php');
        exit;
    }
    
    header('Location: dashboard.php');
    exit;
}

// Intentar login como administrador
$stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ? LIMIT 1");
$stmt->execute([$email_user]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_nombre'] = $admin['nombre']; // Aseguramos que se guarde el nombre para el navbar
    $_SESSION['es_admin'] = true;
    $_SESSION['rol'] = 'admin';
    
    // LA CLAVE AQUÍ: Guardamos el rol numérico (0 para Admin normal, 1 para SuperAdmin)
    $_SESSION['admin_rol'] = $admin['rol']; 
    
    header('Location: admin_dashboard.php');
    exit;
}

header('Location: index.php?error=credenciales');
exit;
?>