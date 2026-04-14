<?php
require_once 'config.php';
$pdo = getConexion();

// Datos del nuevo admin
$nuevo_usuario = 'miguel_Alvadmin'; 
$password_plana = 'itl2026';
$nombre_real = 'Juan Pérez';

// LA CLAVE: Generar el hash dinámicamente
$password_encriptada = password_hash($password_plana, PASSWORD_BCRYPT);

$sql = "INSERT INTO administradores (usuario, password, nombre) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$nuevo_usuario, $password_encriptada, $nombre_real])) {
    echo "¡Administrador creado con éxito y listo para loguearse!";
}
?>