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

            <section class="card checklist-docs">
                <div class="card-header-oro">
                    <h3><i class="fas fa-file-check"></i> Validación de Documentos (Originales)</h3>
                </div>
                <div class="card-body">
                    <form action="actualizar_estatus.php" method="POST">
                        <input type="hidden" name="id_alumno" value="<?= $al['id'] ?>">
                        
                        <label class="check-item">
                            <input type="checkbox"> Acta de Nacimiento (Formato 2025)
                        </label>
                        <label class="check-item">
                            <input type="checkbox"> Certificado de Licenciatura
                        </label>
                        <label class="check-item">
                            <input type="checkbox"> 6 Fotografías (Ovaladas/Mate/Vestimenta Formal)
                        </label>
                        <label class="check-item">
                            <input type="checkbox"> Recibo de Pago de Protocolo
                        </label>
                        <label class="check-item">
                            <input type="checkbox"> Vale de Donación de Libros
                        </label>

                        <hr>
                        <h4>Estatus de Servicios Escolares</h4>
                        <select name="mencion" class="select-pro">
                            <option value="0">Sin Mención Honorífica</option>
                            <option value="1">Candidato a Mención Honorífica</option>
                        </select>
                        
                        <button type="submit" class="btn-guardar">Guardar Cambios en Expediente</button>
                    </form>
                </div>
            </section>
        </div>
    </main>

</body>
</html>