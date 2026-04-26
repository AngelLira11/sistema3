<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilos_registro.css">
    <link rel="stylesheet" href="estilos/alertas.css">
    <title>Registro — ITL Titulación</title>
</head>
<body>

<div id="padre">
    <div id="hijo">

        <h1>Crear Cuenta</h1>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alerta alerta-error">
                <?php
                    $errores = [
                        'campos'     => 'Por favor completa todos los campos.',
                        'duplicado'  => 'El correo o número de control ya están registrados.',
                        'password'   => 'Las contraseñas no coinciden.',
                    ];
                    echo htmlspecialchars($errores[$_GET['error']] ?? 'Error en el registro.');
                ?>
            </div>
        <?php endif; ?>

        <div id="forms">
            <form action="registro_process.php" method="POST">
                <fieldset>
                    <legend>Datos del Alumno</legend>

                    <div class="campo">
                        <label for="nombre">Nombre completo:</label>
                        <input type="text" id="nombre" name="nombre" required
                               placeholder="Nombre completo"
                               value="<?= htmlspecialchars($_GET['nombre'] ?? '') ?>">
                    </div>

                    <div class="campo">
                        <label for="no_control">No. de Control:</label>
                        <input type="text" id="no_control" name="no_control" required
                               placeholder="Ej. 21130001"
                               maxlength="20"
                               value="<?= htmlspecialchars($_GET['no_control'] ?? '') ?>">
                    </div>

                    <div class="campo">
                        <label for="carrera">Carrera:</label>
                        <select id="carrera" name="carrera" required>
                            <option value="" disabled selected>Selecciona tu carrera</option>
                            <option value="ING. SISTEMAS COMPUTACIONALES">ING. SISTEMAS COMPUTACIONALES</option>
                            <option value="ING. ELÉCTRICA">ING. ELÉCTRICA</option>
                            <option value="ING. ELECTRÓNICA">ING. ELECTRÓNICA</option>
                            <option value="ING. INDUSTRIAL">ING. INDUSTRIAL</option>
                            <option value="ING. MECÁNICA">ING. MECÁNICA</option>
                            <option value="ING. MECATRÓNICA">ING. MECATRÓNICA</option>
                            <option value="ING. QUÍMICA">ING. QUÍMICA</option>
                            <option value="ING. GESTIÓN EMPRESARIAL">ING. GESTIÓN EMPRESARIAL</option>
                            <option value="ING. EN ENERGÍAS RENOVABLES">ING. EN ENERGÍAS RENOVABLES</option>
                            <option value="ING. EN SEMICONDUCTORES">ING. EN SEMICONDUCTORES</option>
                            <option value="LIC. ADMINISTRACIÓN">LIC. ADMINISTRACIÓN</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="opcion_titulacion">Opción de Titulación:</label>
                        <select id="opcion_titulacion" name="opcion_titulacion" required>
                            <option value="" disabled selected>Selecciona tu opción</option>
                            <option value="Informe técnico de Residencia Profesional">Informe técnico de Residencia Profesional</option>
                            <option value="Informe de Residencia Profesional">Informe de Residencia Profesional</option>
                            <option value="Proyecto de Investigación y/o Desarrollo Tecnológico">Proyecto de Investigación y/o Desarrollo Tecnológico</option>
                            <option value="Proyecto Integrador">Proyecto Integrador</option>
                            <option value="Proyecto Productivo">Proyecto Productivo</option>
                            <option value="Proyecto de Innovación Tecnológica">Proyecto de Innovación Tecnológica</option>
                            <option value="Proyecto de Emprendedurismo">Proyecto de Emprendedurismo</option>
                            <option value="Proyecto de Educación Dual">Proyecto de Educación Dual</option>
                            <option value="Tesis o Tesina">Tesis o Tesina</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="email">Correo electrónico:</label>
                        <input type="email" id="email" name="email" required
                               placeholder="tu@email.com"
                               value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                    </div>

                    <div class="campo">
                        <label for="celular">Celular:</label>
                        <input type="tel" id="celular" name="celular" required
                               placeholder="Ej. 8711234567"
                               maxlength="15"
                               pattern="[0-9]{10,15}"
                               value="<?= htmlspecialchars($_GET['celular'] ?? '') ?>">
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

                </fieldset>

                <button type="submit">Registrarme</button>
            </form>
        </div>

        <br>
        <a id="enlace" href="index.php"><h3>¿Ya tienes cuenta? Inicia sesión</h3></a>

    </div>
</div>

</body>
</html>
