<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$pdo = getConexion();

$buscar = $_GET['buscar'] ?? '';

$sql = "SELECT anio_egreso, graduacion, id, nombre, no_control, carrera, opcion_titulacion
        FROM alumnos";
$params = [];

if (!empty($buscar)) {
    $sql .= " WHERE nombre LIKE ? OR no_control LIKE ?";
    $params = ["%$buscar%", "%$buscar%"];
}

$sql .= " ORDER BY anio_egreso DESC, graduacion ASC, nombre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar: $grupos[2026]['Graduación 1'] = [alumno, ...]
$grupos = [];
foreach ($rows as $row) {
    $anio = (int)$row['anio_egreso'];
    $grad = $row['graduacion'];
    $grupos[$anio][$grad][] = $row;
}
// Ordenar años descendente (2027 arriba, 2026 abajo)
krsort($grupos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Titulación — ITL</title>
    <link rel="stylesheet" href="estilos/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-content">
            <img src="img/logotipo.png" alt="Logo ITL" class="logo-min">
            <h1>Control de Titulación</h1>
            <div class="user-info">
                <span><i class="fas fa-user-shield"></i> <?= $_SESSION['admin_nombre'] ?? 'Administrador' ?></span>
                <a href="logout.php" class="btn-salir">Salir</a>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="search-section">
            <form method="GET" class="buscador-pro">
                <div class="input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="buscar" placeholder="Buscar por nombre o número de control..." value="<?= htmlspecialchars($buscar) ?>">
                </div>
                <button type="submit" class="btn-buscar">Buscar Estudiante</button>
            </form>
        </section>

        <?php if (empty($grupos)): ?>
            <div class="no-results">No se encontraron estudiantes registrados.</div>
        <?php endif; ?>

        <?php foreach ($grupos as $anio => $graduaciones): ?>
            <div class="generacion-bloque">

                <h2 class="generacion-titulo">
                    <i class="fas fa-graduation-cap"></i> Generación <?= $anio ?>
                </h2>

                <?php foreach (['Graduación 1', 'Graduación 2'] as $grad):
                    if (!isset($graduaciones[$grad])) continue;
                    $lista    = $graduaciones[$grad];
                    $es_grad1 = ($grad === 'Graduación 1');
                ?>
                <section class="generacion-card">
                    <div class="card-header <?= $es_grad1 ? 'grad1' : 'grad2' ?>">
                        <h3>
                            <i class="fas fa-<?= $es_grad1 ? 'sun' : 'snowflake' ?>"></i>
                            <?= $grad ?> — <?= $anio ?>
                        </h3>
                        <span class="badge"><?= count($lista) ?> Alumnos</span>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Control</th>
                                    <th>Nombre Completo</th>
                                    <th>Carrera</th>
                                    <th>Opción de Titulación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista as $al): ?>
                                <tr>
                                    <td class="txt-bold"><?= htmlspecialchars($al['no_control']) ?></td>
                                    <td><?= htmlspecialchars($al['nombre']) ?></td>
                                    <td><span class="carrera-tag"><?= htmlspecialchars($al['carrera']) ?></span></td>
                                    <td><?= htmlspecialchars($al['opcion_titulacion']) ?></td>
                                    <td>
                                        <a href="ver_alumno.php?id=<?= $al['id'] ?>" class="btn-accion">
                                            <i class="fas fa-eye"></i> Revisar Expediente
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php endforeach; ?>

            </div>
        <?php endforeach; ?>
    </main>
</body>
</html>