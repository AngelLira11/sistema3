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
</head>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; font-weight: bold; border: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle"></i> ¡Expediente actualizado correctamente en el sistema!
        </div>
    <?php endif; ?>

    <main class="container">


<body class="bg-light">

    <header class="admin-header">
        <div class="header-content">
            <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
            <h1>Expediente de Titulación</h1>
            <span><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($al['no_control']) ?></span>
        </div>
    </header>

    <main class="container">
        <div class="expediente-grid">
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
                </div>
            </section>

                   </div>
    </main>

</body>
</html>