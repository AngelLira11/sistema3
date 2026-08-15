<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilos_registro.css">
    <link rel="stylesheet" href="estilos/alertas.css">
    <title>Completar Perfil — ITL Titulación</title>
</head>
<body>

<?php
session_start();
require_once 'config.php';
require_once 'profile_check.php';

// Verificar que el usuario esté autenticado
if (empty($_SESSION['alumno_id'])) {
    header('Location: index.php');
    exit;
}

$pdo  = getConexion();
$alumno = getAlumnoById($pdo, $_SESSION['alumno_id']);

if (!$alumno) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Arreglos de opciones
$carreras = [
    'ING. SISTEMAS COMPUTACIONALES',
    'ING. ELÉCTRICA',
    'ING. ELECTRÓNICA',
    'ING. INDUSTRIAL',
    'ING. MECÁNICA',
    'ING. MECATRÓNICA',
    'ING. QUÍMICA',
    'ING. GESTIÓN EMPRESARIAL',
    'ING. EN ENERGÍAS RENOVABLES',
    'ING. EN SEMICONDUCTORES',
    'LIC. ADMINISTRACIÓN',
];

$opciones = [
    'Informe técnico de Residencia Profesional',
    'Informe de Residencia Profesional',
    'Proyecto de Investigación y/o Desarrollo Tecnológico',
    'Proyecto Integrador',
    'Proyecto Productivo',
    'Proyecto de Innovación Tecnológica',
    'Proyecto de Emprendedurismo',
    'Proyecto de Educación Dual',
    'Tesis o Tesina',
    'Otro',
];

// Determinar si es registro nuevo o edición
$es_registro_nuevo = empty($alumno['nombre']) || empty($alumno['no_control']);
?>

<div id="padre">
    <div id="hijo">

        <h1>Completar Perfil</h1>
        <p class="subtitulo">
            <?php if ($es_registro_nuevo): ?>
                Etapa 2 de 2: Información académica
            <?php else: ?>
                Actualizar información académica
            <?php endif; ?>
        </p>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alerta alerta-error">
                <?php
                    $errores = [
                        'campos'     => 'Por favor completa todos los campos requeridos.',
                        'duplicado'  => 'El número de control o correo ya están registrados.',
                        'formato'    => 'Por favor verifica el formato de los datos.',
                    ];
                    echo htmlspecialchars($errores[$_GET['error']] ?? 'Error al actualizar.');
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['success'])): ?>
            <div class="alerta alerta-exito">
                ¡Perfil completado exitosamente! Accediendo al sistema...
            </div>
        <?php endif; ?>

        <div id="forms">
            <form action="complete_profile_process.php" method="POST">
                <fieldset>
                    <legend>Datos Personales</legend>

                    <div class="campo">
                        <label for="nombre">Nombre completo: <span style="color: red;">*</span></label>
                        <input type="text" id="nombre" name="nombre" required
                            placeholder="Nombre completo"
                            value="<?= htmlspecialchars($alumno['nombre']) ?>">
                    </div>

                    <div class="campo">
                        <label for="no_control">No. de Control: <span style="color: red;">*</span></label>
                        <input type="text" id="no_control" name="no_control" required
                            placeholder="Ej. 21130001"
                            maxlength="20"
                            value="">
                    </div>
                    <br>

                </fieldset>

                <fieldset>
                    <legend>Información Académica</legend>

                    <div class="campo">
                        <label for="carrera">Carrera: <span style="color: red;">*</span></label>
                        <select id="carrera" name="carrera" required>
                            <option value="" disabled <?= empty($alumno['carrera']) ? 'selected' : '' ?>>Selecciona tu carrera</option>
                            <?php foreach ($carreras as $c): ?>
                                <option value="<?= $c ?>" <?= $alumno['carrera'] === $c ? 'selected' : '' ?>>
                                    <?= $c ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="opcion_titulacion">Opción de Titulación: <span style="color: red;">*</span></label>
                        <select id="opcion_titulacion" name="opcion_titulacion" required>
                            <option value="" disabled <?= empty($alumno['opcion_titulacion']) ? 'selected' : '' ?>>Selecciona tu opción</option>
                            <?php foreach ($opciones as $o): ?>
                                <option value="<?= $o ?>" <?= $alumno['opcion_titulacion'] === $o ? 'selected' : '' ?>>
                                    <?= $o ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="fecha_egreso">Fecha de egreso: <span style="color: red;">*</span></label>
                        <input type="date" id="fecha_egreso" name="fecha_egreso" required
                            value="<?= htmlspecialchars($alumno['fecha_egreso'] ?? '') ?>">
                        <small>Selecciona la fecha aproximada de tu graduación</small>
                    </div>

                    <div class="campo">
                        <label for="graduacion">Graduación: <span style="color: red;">*</span></label>
                        <select id="graduacion" name="graduacion" required>
                            <option value="" disabled <?= empty($alumno['graduacion']) ? 'selected' : '' ?>>Selecciona tu graduación</option>
                            <option value="1" <?= $alumno['graduacion'] === '1' ? 'selected' : '' ?>>Graduación 1 (Mar-Abr)</option>
                            <option value="2" <?= $alumno['graduacion'] === '2' ? 'selected' : '' ?>>Graduación 2 (Nov-Dic)</option>
                        </select>
                    </div>
                    <br>

                </fieldset>

                <fieldset>
                    <legend>Información de Contacto</legend>

                    <div class="campo">
                        <label for="celular">Celular: <span style="color: red;">*</span></label>
                        <input type="tel" id="celular" name="celular" required
                            placeholder="Ej. 8711234567"
                            maxlength="15"
                            pattern="[0-9]{10,15}"
                            value="<?= htmlspecialchars($alumno['celular']) ?>">
                    </div>

                </fieldset>

                <button type="submit">
                    <?php if ($es_registro_nuevo): ?>
                        Finalizar Registro
                    <?php else: ?>
                        Guardar Cambios
                    <?php endif; ?>
                </button>
            </form>
        </div>

        <br>
        <?php if (!$es_registro_nuevo): ?>
            <a id="enlace" href="dashboard.php"><h3>← Volver</h3></a>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
