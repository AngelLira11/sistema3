<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['alumno_id'])) {
    header('Location: index.php');
    exit;
}

$pdo  = getConexion();
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['alumno_id']]);
$alumno = $stmt->fetch();

if (!$alumno) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$carreras = [
    'ING. SISTEMAS COMPUTACIONALES',
    'ING. ELÉCTRICA','ING. ELECTRÓNICA',
    'ING. INDUSTRIAL','ING. MECÁNICA',
    'ING. MECATRÓNICA','ING. QUÍMICA',
    'ING. GESTIÓN EMPRESARIAL',
    'ING. EN ENERGÍAS RENOVABLES',
    'ING. EN SEMICONDUCTORES',
    'LIC. ADMINISTRACIÓN',
];

$opciones = [
    'Informe técnico de Residencia Profesional',
    'Informe de Residencia Profesional',
    'Proyecto de Investigación y/o Desarrollo Tecnológico',
    'Proyecto Integrador','Proyecto Productivo',
    'Proyecto de Innovación Tecnológica',
    'Proyecto de Emprendedurismo',
    'Proyecto de Educación Dual',
    'Tesis o Tesina','Otro',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilos_registro.css">
    <link rel="stylesheet" href="estilos/alertas.css">
    <title>Editar datos — ITL Titulación</title>
</head>
<body>

<div id="padre">
    <div id="hijo">

        <h1>Editar mis datos</h1>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alerta alerta-error">
                <?php
                    $errores = [
                        'campos'     => 'Por favor completa todos los campos.',
                        'duplicado'  => 'El correo o número de control ya están en uso.',
                        'password'   => 'Las contraseñas no coinciden.',
                    ];
                    echo htmlspecialchars($errores[$_GET['error']] ?? 'Error al actualizar.');
                ?>
            </div>
        <?php endif; ?>

        <div id="forms">
            <form action="editar_process.php" method="POST">
                <fieldset>
                    <div class="campo">
                        <label for="nombre">Nombre completo:</label>
                        <input type="text" id="nombre" name="nombre" required
                            placeholder="Nombre completo"
                            value="<?= htmlspecialchars($alumno['nombre']) ?>">
                    </div>

                    <div class="campo">
                        <label for="no_control">No. de Control:</label>
                        <input type="text" id="no_control" name="no_control" required
                            placeholder="Ej. 21130001"
                            maxlength="20"
                            value="<?= htmlspecialchars($alumno['no_control']) ?>">
                    </div>

                    <div class="campo">
                        <label for="carrera">Carrera:</label>
                        <select id="carrera" name="carrera" required>
                            <option value="" disabled>Selecciona tu carrera</option>
                            <?php foreach ($carreras as $c): ?>
                                <option value="<?= $c ?>" <?= $alumno['carrera'] === $c ? 'selected' : '' ?>>
                                    <?= $c ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="opcion_titulacion">Opción de Titulación:</label>
                        <select id="opcion_titulacion" name="opcion_titulacion" required>
                            <option value="" disabled>Selecciona tu opción</option>
                            <?php foreach ($opciones as $o): ?>
                                <option value="<?= $o ?>" <?= $alumno['opcion_titulacion'] === $o ? 'selected' : '' ?>>
                                    <?= $o ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="email">Correo electrónico:</label>
                        <input type="email" id="email" name="email" required
                            placeholder="tu@email.com"
                            value="<?= htmlspecialchars($alumno['email']) ?>">
                    </div>

                    <div class="campo">
                        <label for="celular">Celular:</label>
                        <input type="tel" id="celular" name="celular" required
                            placeholder="Ej. 8711234567"
                            maxlength="15"
                            pattern="[0-9]{10,15}"
                            value="<?= htmlspecialchars($alumno['celular']) ?>">
                    </div>

                    <div class="campo">
                        <label for="fecha_egreso">Fecha de egreso:</label>
                        <input type="date" id="fecha_egreso" name="fecha_egreso" required
                            value="<?= htmlspecialchars($alumno['fecha_egreso']) ?>">
                        <small>Selecciona la fecha aproximada de tu graduación</small>
                    </div>

                    <div class="campo">
                        <label for="graduacion">Graduación:</label>
                        <select id="graduacion" name="graduacion" required>
                            <option value="" disabled>Selecciona tu graduación</option>
                            <option value="1" <?= $alumno['graduacion'] === '1' ? 'selected' : '' ?>>Graduación 1 (Mar-Abr)</option>
                            <option value="2" <?= $alumno['graduacion'] === '2' ? 'selected' : '' ?>>Graduación 2 (Nov-Dic)</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="password">Nueva contraseña (opcional):</label>
                        <input type="password" id="password" name="password"
                            placeholder="Dejar vacío para no cambiar"
                            minlength="8">
                    </div>

                    <div class="campo">
                        <label for="password2">Confirmar contraseña:</label>
                        <input type="password" id="password2" name="password2"
                            placeholder="Repite la nueva contraseña"
                            minlength="8">
                    </div>
                </fieldset>
                <button type="submit">Guardar cambios</button>
            </form>
        </div>
        <br>
        <a id="enlace" href="dashboard.php"><h3>← Volver al inicio</h3></a>
    </div>
</div>

</body>
</html>