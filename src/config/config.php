<?php

$projectRoot = dirname(__DIR__, 2);

// Cargar .env con las funciones incluidas en PHP; no requiere Composer.
if (is_file($projectRoot . '/.env')) {
    $environment = parse_ini_file($projectRoot . '/.env', false, INI_SCANNER_RAW);
    if (is_array($environment)) {
        foreach ($environment as $key => $value) {
            if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// 2. Helper para obtener variables de entorno con valor por defecto
function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return $value !== false && $value !== null ? $value : $default;
}

// 3. Determinar dinámicamente el sufijo (_LOCAL o _REMOTE)
$envType = strtoupper(env('DB_ENVIRONMENT', 'LOCAL'));
$suffix  = ($envType === 'REMOTE') ? '_REMOTE' : '_LOCAL';

// 4. Constante de entorno de la aplicación
define('APP_ENV', $envType);

// 5. Definición de constantes para la Base de Datos
define('DB_HOST', env('DB_HOST' . $suffix, 'localhost'));
define('DB_USER', env('DB_USER' . $suffix, 'root'));
define('DB_PASS', env('DB_PASS' . $suffix, ''));
define('DB_NAME', env('DB_NAME' . $suffix, 'titulacion'));
define('DB_PORT', (int) env('DB_PORT' . $suffix, 3306));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));
define('DB_SSL_CA', env('DB_SSL_CA', __DIR__ . '/isrgrootx1.pem'));

// 5. Conexión Singleton con PDO
function getConexion(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Habilitar SSL obligatoriamente para entornos remotos (como TiDB Cloud)
        if (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1') {
            $opciones[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
            $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Error de conexión a la base de datos. ' . $e->getMessage()]));
        }
    }
    return $pdo;
}