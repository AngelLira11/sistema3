<?php
session_start();
require_once 'config.php';

$email_user = trim($_POST['email'] ?? '');
$password   = trim($_POST['password'] ?? '');

$pdo = getConexion();

// PASO 1: Buscar en Alumnos
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE email = ? LIMIT 1");
$stmt->execute([$email_user]);
$alumno = $stmt->fetch();

if ($alumno && password_verify($password, $alumno['password'])) {
    $_SESSION['alumno_id'] = $alumno['id'];
    $_SESSION['rol'] = 'alumno';
    header('Location: dashboard.php');
    exit;
}

// PASO 2: Buscar en Administradores
$stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ? LIMIT 1");
$stmt->execute([$email_user]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['es_admin'] = true;
    $_SESSION['rol'] = 'admin';
    header('Location: admin_dashboard.php');
    exit;
}

// Si falla, regresa al index con error
header('Location: index.php?error=credenciales');
exit;