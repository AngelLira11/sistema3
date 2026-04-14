<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilos.css">
    <link rel="stylesheet" href="estilos/alertas.css">
    <title>Login — ITL Titulación</title>
</head>
<body>

<div id="padre">
    <div id="hijo">

        <div id="logo">
            <img src="img/logotipo.png" id="log" alt="Logo ITL">
        </div>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alerta alerta-error">
                <?php
                    $errores = [
                        'credenciales' => 'Correo o contraseña incorrectos.',
                        'campos'       => 'Por favor llena todos los campos.',
                    ];
                    echo htmlspecialchars($errores[$_GET['error']] ?? 'Error desconocido.');
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['registro'])): ?>
            <div class="alerta alerta-ok">
                ¡Registro exitoso! Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <div id="forms">
            <form action="login_process.php" method="POST">
                <fieldset>
                    <legend>Iniciar Sesión</legend>

                    <div class="campo">
                        <label for="email">Correo:</label>
                        <input type="text" id="email" name="email" required placeholder="tu@email.com" autocomplete="email">
                    </div>

                    <div class="campo">
                        <label for="password">Contraseña:</label>
                        <input type="password" id="password" name="password" required placeholder="Tu contraseña">
                    </div>

                    <button type="submit">Entrar</button>
                </fieldset>
            </form>
        </div>

        <br>
        <a id="enlace" href="registro.php"><h3>¿No tienes cuenta? Regístrate</h3></a>

    </div>
</div>

</body>
</html>
