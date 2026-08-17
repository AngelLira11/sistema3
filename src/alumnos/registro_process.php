<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/auth/csrf.php';

use Gregwar\Captcha\PhraseBuilder;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/registro.php');
    exit;
}

csrf_validate();

/**
 * Rate Limiting Helper Functions
 */
function getRateLimitFile($ip) {
    return sys_get_temp_dir() . '/registro_ratelimit_' . md5($ip) . '.json';
}

function checkRateLimit($ip) {
    if (APP_ENV === 'LOCAL') return true;
    $file = getRateLimitFile($ip);
    $now = time();
    $window = 900; // 15 minutos
    $max_attempts = 5;
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: ['attempts' => []];
        
        // Limpiar intentos viejos (fuera de la ventana de tiempo)
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
        
        // Verificar si excedió el límite
        if (count($data['attempts']) >= $max_attempts) {
            return false; // Bloqueado
        }
    } else {
        $data = ['attempts' => []];
    }
    
    return true; // Permitido
}

function recordFailedAttempt($ip) {
    if (APP_ENV === 'LOCAL') return;
    $file = getRateLimitFile($ip);
    $now = time();
    $window = 900; // 15 minutos
    
    $data = file_exists($file) 
        ? json_decode(file_get_contents($file), true) ?: ['attempts' => []]
        : ['attempts' => []];
    
    // Limpiar intentos viejos
    $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    // Agregar nuevo intento
    $data['attempts'][] = $now;
    
    file_put_contents($file, json_encode($data), LOCK_EX);
}

function clearRateLimit($ip) {
    if (APP_ENV === 'LOCAL') return;
    $file = getRateLimitFile($ip);
    if (file_exists($file)) {
        unlink($file);
    }
}

// Obtener IP del cliente
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Verificar rate limit
if (!checkRateLimit($client_ip)) {
    header('Location: ../../public/registro.php?error=bloqueado');
    exit;
}

// Recoger y limpiar datos - SOLO LO NECESARIO PARA LA ETAPA 1
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password']  ?? '';
$password2 = $_POST['password2'] ?? '';
$captcha   = trim($_POST['captcha_input'] ?? '');
$form_timestamp = (int)($_POST['form_timestamp'] ?? 0);

// Validar que el formulario fue cargado
if (empty($form_timestamp)) {
    recordFailedAttempt($client_ip);
    header('Location: ../../public/registro.php?error=campos&email=' . urlencode($email));
    exit;
}

// Verificar que pasó mínimo 5 segundos desde que se cargó el formulario
$time_elapsed = time() - $form_timestamp;
if ($time_elapsed < 5) {
    recordFailedAttempt($client_ip);
    header('Location: ../../public/registro.php?error=demasiado_rapido&email=' . urlencode($email));
    exit;
}

// Validar campos requeridos
if (empty($email) || empty($password) || empty($password2) || empty($captcha)) {
    recordFailedAttempt($client_ip);
    header('Location: ../../public/registro.php?error=campos&email=' . urlencode($email));
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    recordFailedAttempt($client_ip);
    header('Location: ../../public/registro.php?error=campos&email=' . urlencode($email));
    exit;
}

// Validar contraseñas iguales
if ($password !== $password2) {
    recordFailedAttempt($client_ip);
    header('Location: ../../public/registro.php?error=password&email=' . urlencode($email));
    exit;
}

// Validar CAPTCHA
$captcha_user    = trim($_POST['captcha'] ?? $captcha ?? '');
$captcha_session = $_SESSION['captcha_phrase'] ?? '';

// strcasecmp devuelve 0 cuando las dos cadenas coinciden (sin importar mayúsculas/minúsculas)
if (empty($captcha_user) || empty($captcha_session) || strcasecmp($captcha_session, $captcha_user) !== 0) {
    // Limpiar el CAPTCHA para evitar reutilización
    unset($_SESSION['captcha_phrase']);
    
    recordFailedAttempt($client_ip);
    header('Location: ../../public/registro.php?error=captcha&email=' . urlencode($email));
    exit;
}

// Limpiar la sesión si el CAPTCHA fue correcto
unset($_SESSION['captcha_phrase']);

try {
    $pdo = getConexion();

    // Verificar si el email ya está registrado
    $check = $pdo->prepare("SELECT id FROM alumnos WHERE email = ? LIMIT 1");
    $check->execute([$email]);
    if ($check->fetch()) {
        recordFailedAttempt($client_ip);
        header('Location: ../../public/registro.php?error=duplicado&email=' . urlencode($email));
        exit;
    }

    // Insertar alumno con campos mínimos
    // Los campos opcionales se dejarán vacíos o con valores por defecto válidos
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Generar un no_control temporal único (será reemplazado en Etapa 2)
    // Debe caber en 20 caracteres (límite de BD)
    $no_control_temporal = 'TEMP_' . uniqid();
    
    $stmt = $pdo->prepare("
        INSERT INTO alumnos (
            nombre, no_control, carrera, opcion_titulacion, 
            email, celular, fecha_egreso, graduacion, anio_egreso, 
            password, mencion_honorifica
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Los campos se envían con valores apropiados
    // no_control: valor temporal único (será reemplazado en Etapa 2)
    // fecha_egreso: fecha de hoy como placeholder
    $stmt->execute([
        '',                      // nombre - vacío
        $no_control_temporal,    // no_control - temporal único (será actualizado en Etapa 2)
        '',                      // carrera - vacío
        '',                      // opcion_titulacion - vacío
        $email,                  // email
        '',                      // celular - vacío
        date('Y-m-d'),           // fecha_egreso - fecha de hoy (será actualizada en Etapa 2)
        '1',                     // graduacion - valor válido por defecto ('1' o '2')
        date('Y'),               // anio_egreso - año actual
        $hash,                   // password
        0                        // mencion_honorifica - falso por defecto
    ]);

    // Obtener el ID del alumno recién creado
    $alumno_id = $pdo->lastInsertId();

    // Iniciar sesión automáticamente
    $_SESSION['alumno_id'] = $alumno_id;
    $_SESSION['rol'] = 'alumno';
    
    // Limpiar rate limit y form_load_time para este cliente
    clearRateLimit($client_ip);
    unset($_SESSION['form_load_time']);

    // Redirigir a completar perfil
    header('Location: ../../public/complete_profile.php');
    exit;

} catch (Exception $e) {
    // header('Location: registro.php?error=campos');
    // exit;
    die($e->getMessage()); // Línea para debug (borrar en producción)
}
?>
