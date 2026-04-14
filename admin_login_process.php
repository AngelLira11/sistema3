<?php
session_start();
require_once 'config.php';

// Solo aceptamos peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_login.php');
    exit;
}

$usuario_input = trim($_POST['usuario'] ?? '');
$password_input = trim($_POST['password'] ?? '');

if (empty($usuario_input) || empty($password_input)) {
    header('Location: admin_login.php?error=vacio');
    exit;
}

try {
    $pdo = getConexion();
    
    // 1. Sentencia Preparada: Buscamos al administrador por su nombre de usuario
    $stmt = $pdo->prepare("SELECT id, usuario, password, nombre FROM administradores WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario_input]);
    $admin = $stmt->fetch();

    // 2. Verificación de seguridad
    if ($admin && password_verify($password_input, $admin['password'])) {
        // Regenerar el ID de sesión es una buena práctica contra "Session Fixation"
        session_regenerate_id(true);

        // Guardamos los datos en la sesión
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['es_admin'] = true;

        header('Location: admin_dashboard.php');
        exit;
    } else {
        // Credenciales incorrectas
        header('Location: admin_login.php?error=auth');
        exit;
    }

} catch (Exception $e) {
    // Error de sistema
    header('Location: admin_login.php?error=sistema');
    exit;
}