<?php
// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// Instituto Tecnológico de La Laguna
// ============================================

define('DB_HOST',   'localhost');
define('DB_USER',   'root');        // Cambia si tu usuario de MySQL es diferente
define('DB_PASS',   '');            // Cambia si tienes contraseña en MySQL
define('DB_NAME',   'itl_titulacion');
define('DB_CHARSET','utf8mb4');

function getConexion(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Error de conexión a la base de datos.']));
        }
    }
    return $pdo;
}
?>
