<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/estilos_dashboard.css">
    <link rel="stylesheet" href="assets/css/alertas.css">
    <title>Mi Trámite — ITL</title>
</head>
<body>

<?php
session_start();
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/auth/profile_check.php';

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

// Verificar si el perfil está incompleto
redirectIfIncomplete($alumno);
?>

<div class="header">
    <img src="img/logotipo.png" alt="Logo ITL" class="logo-header">
    <div class="header-titulo">
        <h2>Sistema de Titulación</h2>
        <p>Instituto Tecnológico de La Laguna</p>
    </div>
    <a href="../src/auth/logout.php" class="btn-logout">Cerrar sesión</a>
</div>

<div class="contenedor">

    <?php if (!empty($_GET['profile_completed'])): ?>
        <div class="alerta alerta-exito">
            ¡Perfil completado exitosamente! Ya puedes acceder a todas las funciones.
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['ok'])): ?>
        <div class="alerta alerta-exito">
            Tus datos han sido actualizados correctamente.
        </div>
    <?php endif; ?>

    <div class="bienvenida">
        <h1>¡Hola, <?= htmlspecialchars($alumno['nombre']) ?>!</h1>
        <p>Aquí puedes consultar tus datos y generar tu constancia para titulación.</p>
    </div>

    <div class="tarjeta">
        <h3>📋 Tus datos registrados</h3>
        <div class="datos-grid">
            <div class="dato">
                <span class="etiqueta">Nombre completo</span>
                <span class="valor"><?= htmlspecialchars($alumno['nombre']) ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">No. de Control</span>
                <span class="valor"><?= htmlspecialchars($alumno['no_control']) ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">Carrera</span>
                <span class="valor"><?= htmlspecialchars($alumno['carrera']) ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">Opción de Titulación</span>
                <span class="valor"><?= htmlspecialchars($alumno['opcion_titulacion']) ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">Correo electrónico</span>
                <span class="valor"><?= htmlspecialchars($alumno['email']) ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">Celular</span>
                <span class="valor"><?= htmlspecialchars($alumno['celular']) ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">Graduación</span>
                <span class="valor"><?= htmlspecialchars($alumno['graduacion']) ?> — <?= (int)$alumno['anio_egreso'] ?></span>
            </div>
            <div class="dato">
                <span class="etiqueta">Fecha de Egreso</span>
                <span class="valor"><?= date('d/m/Y', strtotime($alumno['fecha_egreso'])) ?></span>
            </div>
        </div>
    </div>

    <div class="acciones">
        <a href="editar_datos.php" class="btn-editar">
            ✏️ Editar mis datos
        </a>
        <a href="../src/alumnos/generar_constancia.php" class="btn-pdf" target="_blank">
            🖨️ Generar Constancia de No Inconveniencia
        </a>
        <p class="nota">El documento se abre directo en el navegador listo para imprimir o guardar como PDF.</p>
    </div>

</div>

</body>
</html>
