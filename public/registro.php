<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/estilos_registro.css">
    <link rel="stylesheet" href="assets/css/alertas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Registro — ITL Titulación</title>
</head>
<body>

<?php
session_start();
require_once __DIR__ . '/../src/auth/csrf.php';

// Si hay un error, resetear el contador (permitir que reintente)
if (!empty($_GET['error'])) {
    $_SESSION['form_load_time'] = time();
} elseif (empty($_SESSION['form_load_time'])) {
    // Generar timestamp cuando se carga el formulario por primera vez
    $_SESSION['form_load_time'] = time();
}
?>

<div id="padre">
    <div id="hijo">

        <h1>Crear Cuenta</h1>
        <p class="subtitulo">Etapa 1 de 2: Crea tu cuenta</p>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alerta alerta-error">
                <?php
                    $errores = [
                        'campos'     => 'Por favor completa todos los campos.',
                        'duplicado'  => 'El correo ya está registrado.',
                        'password'   => 'Las contraseñas no coinciden.',
                        'captcha'    => 'El código CAPTCHA es incorrecto. Intenta de nuevo.',
                        'demasiado_rapido' => 'Completaste el formulario muy rápido. Por favor, intenta de nuevo.',
                        'bloqueado'  => 'Demasiados intentos fallidos. Intenta más tarde.',
                    ];
                    echo htmlspecialchars($errores[$_GET['error']] ?? 'Error en el registro.');
                ?>
            </div>
        <?php endif; ?>

        <div id="forms">
            <form action="../src/alumnos/registro_process.php" method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="form_timestamp" value="<?php echo $_SESSION['form_load_time'] ?? time(); ?>">
                
                <fieldset>
                    <legend>Información de Cuenta</legend>

                    <div class="campo">
                        <label for="email">Correo electrónico:</label>
                        <input type="email" id="email" name="email" required
                               placeholder="tu@email.com"
                               value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                        <small>Usarás este correo para iniciar sesión</small>
                    </div>

                    <div class="campo">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Mínimo 8 caracteres"
                               minlength="8">
                    </div>

                    <div class="campo">
                        <label for="password2">Confirmar contraseña:</label>
                        <input type="password" id="password2" name="password2" required
                               placeholder="Repite tu contraseña"
                               minlength="8">
                    </div>
                    <br>
                    <fieldset class="captcha-fieldset">
                        <div class="captcha-container">
                            <legend class="captcha-legend">Verifica que no eres un robot</legend>
                            
                            <div class="captcha-image-wrapper">
                                <img id="captcha-image" src="../src/captcha/captcha.php?t=<?php echo time(); ?>" 
                                     alt="CAPTCHA"
                                     class="captcha-image">
                                <button type="button" id="reload-captcha" class="captcha-reload" 
                                        title="Generar nuevo CAPTCHA">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="captcha_input">Ingresa el código que ves arriba:</label>
                            <input type="text" id="captcha_input" name="captcha_input" required
                                   placeholder="6 caracteres"
                                   maxlength="6"
                                   autocomplete="off"
                                   class="captcha-input">
                        </div>
                    </fieldset>

                </fieldset>

                <button type="submit">Continuar</button>
            </form>
        </div>

        <br>
        <a id="enlace" href="index.php"><h3>¿Ya tienes cuenta? Inicia sesión</h3></a>

    </div>
</div>

<script>
/**
 * CAPTCHA Reload Handler
 * Recarga la imagen CAPTCHA sin recargar la página
 */
document.addEventListener('DOMContentLoaded', function() {
    const reloadBtn = document.getElementById('reload-captcha');
    const captchaImg = document.getElementById('captcha-image');
    
    if (reloadBtn && captchaImg) {
        reloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Obtener nuevo timestamp para cache-busting
            fetch('../src/captcha/reload_captcha.php')
                .then(response => response.json())
                .then(data => {
                    // Recargar imagen con nuevo timestamp
                    captchaImg.src = '../src/captcha/captcha.php?t=' + data.timestamp;
                    
                    // Limpiar campo de entrada
                    document.getElementById('captcha_input').value = '';
                    document.getElementById('captcha_input').focus();
                })
                .catch(error => {
                    console.error('Error recargando CAPTCHA:', error);
                    // Fallback: recargar manualmente
                    captchaImg.src = '../src/captcha/captcha.php?t=' + new Date().getTime();
                    document.getElementById('captcha_input').value = '';
                });
        });
    }
});
</script>

</body>
</html>
