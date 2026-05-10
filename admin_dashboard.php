<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$pdo = getConexion();

$buscar   = $_GET['buscar']            ?? '';
$carrera  = $_GET['carrera']           ?? '';
$opcion   = $_GET['opcion_titulacion'] ?? '';
$anio_f   = $_GET['anio']             ?? '';
$grad_f   = $_GET['graduacion']        ?? '';

$sql    = "SELECT anio_egreso, graduacion, id, nombre, no_control, carrera, opcion_titulacion FROM alumnos WHERE 1=1";
$params = [];

if (!empty($buscar)) {
    $sql .= " AND (nombre LIKE ? OR no_control LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
}
if (!empty($carrera)) {
    $sql .= " AND carrera = ?";
    $params[] = $carrera;
}
if (!empty($opcion)) {
    $sql .= " AND opcion_titulacion = ?";
    $params[] = $opcion;
}
if (!empty($anio_f)) {
    $sql .= " AND anio_egreso = ?";
    $params[] = $anio_f;
}
if (!empty($grad_f)) {
    $sql .= " AND graduacion = ?";
    $params[] = $grad_f;
}

$sql .= " ORDER BY anio_egreso DESC, graduacion ASC, nombre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener años disponibles para el dropdown
$anios = $pdo->query("SELECT DISTINCT anio_egreso FROM alumnos ORDER BY anio_egreso DESC")->fetchAll(PDO::FETCH_COLUMN);

$grupos = [];
foreach ($rows as $row) {
    $grupos[(int)$row['anio_egreso']][$row['graduacion']][] = $row;
}
krsort($grupos);

$carreras = [
    'ING. SISTEMAS COMPUTACIONALES','ING. ELÉCTRICA','ING. ELECTRÓNICA',
    'ING. INDUSTRIAL','ING. MECÁNICA','ING. MECATRÓNICA','ING. QUÍMICA',
    'ING. GESTIÓN EMPRESARIAL','ING. EN ENERGÍAS RENOVABLES',
    'ING. EN SEMICONDUCTORES','LIC. ADMINISTRACIÓN',
];

$opciones = [
    'Informe técnico de Residencia Profesional',
    'Informe de Residencia Profesional',
    'Proyecto de Investigación y/o Desarrollo Tecnológico',
    'Proyecto Integrador','Proyecto Productivo',
    'Proyecto de Innovación Tecnológica','Proyecto de Emprendedurismo',
    'Proyecto de Educación Dual','Tesis o Tesina','Otro',
];
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
                    <input type="text" name="buscar"
                           placeholder="Buscar por nombre o número de control..."
                           value="<?= htmlspecialchars($buscar) ?>">
                </div>

                <div class="filtros-grid">
                    <select name="carrera">
                        <option value="">Todas las carreras</option>
                        <?php foreach ($carreras as $c): ?>
                            <option value="<?= $c ?>" <?= $carrera === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="opcion_titulacion">
                        <option value="">Todas las opciones</option>
                        <?php foreach ($opciones as $o): ?>
                            <option value="<?= $o ?>" <?= $opcion === $o ? 'selected' : '' ?>><?= $o ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="anio">
                        <option value="">Todos los años</option>
                        <?php foreach ($anios as $a): ?>
                            <option value="<?= $a ?>" <?= $anio_f == $a ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="graduacion">
                        <option value="">Ambas graduaciones</option>
                        <option value="1" <?= $grad_f === '1' ? 'selected' : '' ?>>Graduación 1 (Mar-Abr)</option>
                        <option value="2" <?= $grad_f === '2' ? 'selected' : '' ?>>Graduación 2 (Nov-Dic)</option>
                    </select>
                </div>

                <div class="filtros-acciones">
                    <button type="submit" class="btn-buscar">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="admin_dashboard.php" class="btn-limpiar">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>

            </form>
        </section>

        <?php if (empty($grupos)): ?>
            <div class="no-results">No se encontraron estudiantes con esos filtros.</div>
        <?php endif; ?>

        <?php foreach ($grupos as $anio => $graduaciones): ?>
            <div class="generacion-bloque">
                <h2 class="generacion-titulo">
                    <i class="fas fa-graduation-cap"></i> Generación <?= $anio ?>
                </h2>

                <?php foreach (['1', '2'] as $grad):
                    if (!isset($graduaciones[$grad])) continue;
                    $lista    = $graduaciones[$grad];
                    $es_grad1 = ($grad === '1');
                ?>
                <section class="generacion-card">
                    <div class="card-header <?= $es_grad1 ? 'grad1' : 'grad2' ?>">
                        <h3>
                            <i class="fas fa-<?= $es_grad1 ? 'sun' : 'snowflake' ?>"></i>
                            <?= $es_grad1 ? 'Graduación 1 (Mar-Abr)' : 'Graduación 2 (Nov-Dic)' ?> — <?= $anio ?>
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

        <div class="exportar-section">
            <a href="exportar_excel.php?<?= http_build_query($_GET) ?>" class="btn-exportar">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </a>
        </div>
    </main>
</body>
</html>