<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$id = $_GET['id'] ?? 0;
$pdo = getConexion();

// Consultar datos del alumno
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id = ?");
$stmt->execute([$id]);
$al = $stmt->fetch();

if (!$al) {
    die("Estudiante no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Expediente: <?= htmlspecialchars($al['nombre']) ?> — ITL</title>
    <link rel="stylesheet" href="estilos/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Estilos añadidos para centrar y despegar la ficha -->
    <style>
        .expediente-container {
            max-width: 800px; /* Limita el ancho de la ficha para que no se estire demasiado */
            margin: 40px auto; /* 40px de separación arriba/abajo y 'auto' la centra horizontalmente */
            padding: 0 20px; /* Colchón a los lados para pantallas pequeñas */
        }
        
        .card.info-personal {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); /* Sombra suave para darle presencia */
            overflow: hidden;
        }

        .card-body p {
            margin-bottom: 12px; /* Más espacio entre cada dato */
            font-size: 16px;
        }
    </style>
</head>

<body class="bg-light">

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 800px; border-radius: 5px; text-align: center; font-weight: bold; border: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> ¡Expediente actualizado correctamente en el sistema!
        </div>
    <?php endif; ?>

    <header class="admin-header">
        <div class="header-content">
            <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
            <h1>Expediente de Titulación</h1>
            <span><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($al['no_control']) ?></span>
        </div>
    </header>

    <!-- Contenedor principal corregido y centrado -->
    <main class="expediente-container">
        <section class="card info-personal">
            <div class="card-header-oro">
                <h3><i class="fas fa-address-card"></i> Datos Generales</h3>
            </div>
            <div class="card-body">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($al['nombre']) ?></p>
                <p><strong>Carrera:</strong> <?= htmlspecialchars($al['carrera']) ?></p>
                <p><strong>Opción:</strong> <?= htmlspecialchars($al['opcion_titulacion']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($al['email']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($al['celular']) ?></p>
                <p><strong>Graduación:</strong>
                    <?= htmlspecialchars($al['graduacion']) ?> — <?= (int)$al['anio_egreso'] ?>
                </p>
                <p><strong>Fecha de egreso:</strong>
                    <?= date('d/m/Y', strtotime($al['fecha_egreso'])) ?>
                </p>
            </div>
        </section>
    </main>

</body>
</html>