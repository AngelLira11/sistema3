<?php
require_once 'config.php';
$pdo = getConexion();

// Datos del nuevo admin
$nuevo_usuario = 'miguel_alv'; 
$password_plana = 'itltitulacion26';
$nombre_real = 'Miguel Alvarez';

// LA CLAVE: Generar el hash dinámicamente
$password_encriptada = password_hash($password_plana, PASSWORD_BCRYPT);

$sql = "INSERT INTO administradores (usuario, password, nombre) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);

if($stmt->execute([$nuevo_usuario, $password_encriptada, $nombre_real])) {
    echo "¡Administrador creado con éxito y listo para loguearse!";
}
?>


<!--Admin Angel Lira
$nuevo_usuario = 'jose_angel'; 
$password_plana = '81132329';
$nombre_real = 'Angel Lira';

Admin Miguel Alvarez
$nuevo_usuario = 'miguel_alv'; 
$password_plana = 'itltitulacion26';
$nombre_real = 'Miguel Alvarez';




Cuentas a dar de alta

Nombre: Laura    Contraseña: ItlTitulacionAlumnos
Nombre: Karina   Contraseña: ItlTitulacionAlumnos
Nombre: Vanessa  Contraseña: ItlTitulacionAlumnos 
Nombre: Myrna    Contraseña: ItlTitulacionAlumnos 
-->