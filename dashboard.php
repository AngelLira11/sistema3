<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilos_dashboard.css">
    <title>Mi Trámite — ITL</title>
</head>
<body>

<?php
session_start();
require_once 'config.php';

// Proteger página: solo alumnos con sesión activa
if (empty($_SESSION['alumno_id'])) {
    header('Location: index.php');
    exit;
}

// Obtener datos actualizados del alumno
$pdo  = getConexion();
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['alumno_id']]);
$alumno = $stmt->fetch();

if (!$alumno) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<div class="header">
    <img src="img/logotipo.png" alt="Logo ITL" class="logo-header">
    <div class="header-titulo">
        <h2>Sistema de Titulación</h2>
        <p>Instituto Tecnológico de La Laguna</p>
    </div>
    <a href="logout.php" class="btn-logout">Cerrar sesión</a>
</div>

<div class="contenedor">

    <div class="bienvenida">
        <h1>Bienvenido, <?= htmlspecialchars($alumno['nombre']) ?></h1>
        <p>Aquí puedes consultar tus datos y generar tu constancia para titulación.</p>
    </div>

    <!-- Tarjeta de datos del alumno -->
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
        </div>
    </div>

    <!-- Botón generar constancia -->
    <div class="acciones">
        <a href="generar_constancia.php" class="btn-pdf" target="_blank">
            🖨️ Generar Constancia de No Inconveniencia
        </a>
        <p class="nota">El documento se abre directo en el navegador listo para imprimir o guardar como PDF.</p>
    </div>

</div>

</body>
</html>
