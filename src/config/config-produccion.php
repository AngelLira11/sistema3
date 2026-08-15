<?php
//db
define('DB_HOST',   'gateway01.us-east-1.prod.aws.tidbcloud.com');
define('DB_USER',   '3uSsvXzWqJayFsh.root');        // Cambia si tu usuario de MySQL es diferente
define('DB_PASS',   'mSxyPOXt5uE2dswB');            // Cambia si tienes contraseña en MySQL
define('DB_NAME',   'titulacion_db');
define('DB_CHARSET','utf8mb4');

function getConexion(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=4000;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            
            PDO::MYSQL_ATTR_SSL_CA		 => __DIR__ . '/isrgrootx1.pem',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Error de conexión a la base de datos. ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
?>