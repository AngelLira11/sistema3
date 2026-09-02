<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/admin_login.php');
    exit;
}

csrf_validate();

$usuario_input = trim($_POST['usuario'] ?? '');
$password_input = trim($_POST['password'] ?? '');

if (empty($usuario_input) || empty($password_input)) {
    header('Location: ../../public/admin_login.php?error=vacio');
    exit;
}

try {
    $pdo = getConexion();
    
    $stmt = $pdo->prepare("SELECT id, usuario, password, nombre, rol FROM administradores WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario_input]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password_input, $admin['password'])) {
        session_regenerate_id(true);

        // Guardamos los datos en la sesión
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_rol'] = (int)$admin['rol'];
        $_SESSION['es_admin'] = true;

        header('Location: ../../public/admin_dashboard.php');
        exit;
    } else {
        // Credenciales incorrectas
        header('Location: ../../public/admin_login.php?error=auth');
        exit;
    }

} catch (Exception $e) {
    // Error de sistema
    header('Location: ../../public/admin_login.php?error=sistema');
    exit;
}