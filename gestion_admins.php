<?php
session_start();
require_once 'config.php';

// Solo SuperAdmin (rol = 1)
if (empty($_SESSION['admin_id']) || ($_SESSION['admin_rol'] ?? 0) != 1) {
    header('Location: admin_dashboard.php');
    exit;
}

$pdo     = getConexion();
$mensaje = '';

// ── 1. DAR DE ALTA ────────────────────────────────────────────────────────────
if (isset($_POST['registrar_admin'])) {
    $nuevo_usuario  = trim($_POST['usuario']  ?? '');
    $password_plana = trim($_POST['password'] ?? '');
    $nombre_real    = trim($_POST['nombre']   ?? '');

    if (empty($nuevo_usuario) || empty($password_plana) || empty($nombre_real)) {
        $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> Completa todos los campos.</div>";
    } else {
        // Verificar que el usuario no exista
        $chk = $pdo->prepare("SELECT id FROM administradores WHERE usuario = ? LIMIT 1");
        $chk->execute([$nuevo_usuario]);
        if ($chk->fetch()) {
            $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> Ese nombre de usuario ya existe.</div>";
        } else {
            $hash = password_hash($password_plana, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO administradores (usuario, password, nombre, rol) VALUES (?, ?, ?, 0)");
            if ($stmt->execute([$nuevo_usuario, $hash, $nombre_real])) {
                $mensaje = "<div class='alert success'><i class='fas fa-check-circle'></i> Administrador <strong>$nuevo_usuario</strong> creado con éxito.</div>";
            } else {
                $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> Error al crear el administrador.</div>";
            }
        }
    }
}

// ── 2. CAMBIAR CONTRASEÑA ────────────────────────────────────────────────────
if (isset($_POST['cambiar_password'])) {
    $admin_id       = (int)($_POST['id_admin']        ?? 0);
    $nueva_pass_txt = trim($_POST['nueva_password']   ?? '');

    if (!$admin_id || empty($nueva_pass_txt)) {
        $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> Datos incompletos.</div>";
    } elseif (strlen($nueva_pass_txt) < 6) {
        $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> La contraseña debe tener al menos 6 caracteres.</div>";
    } else {
        $hash = password_hash($nueva_pass_txt, PASSWORD_BCRYPT);
        // Solo admins normales (rol = 0), nunca al propio superadmin
        $stmt = $pdo->prepare("UPDATE administradores SET password = ? WHERE id = ? AND rol = 0");
        if ($stmt->execute([$hash, $admin_id]) && $stmt->rowCount() > 0) {
            $mensaje = "<div class='alert success'><i class='fas fa-check-circle'></i> Contraseña actualizada correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> No se pudo actualizar. Verifica el administrador.</div>";
        }
    }
}

// ── 3. DAR DE BAJA ───────────────────────────────────────────────────────────
if (isset($_POST['eliminar_admin'])) {
    $admin_id = (int)($_POST['id_admin'] ?? 0);

    if (!$admin_id) {
        $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> ID inválido.</div>";
    } elseif ($admin_id == (int)$_SESSION['admin_id']) {
        $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> No puedes eliminar tu propia cuenta.</div>";
    } else {
        // Solo admins normales (rol = 0)
        $stmt = $pdo->prepare("DELETE FROM administradores WHERE id = ? AND rol = 0");
        if ($stmt->execute([$admin_id]) && $stmt->rowCount() > 0) {
            $mensaje = "<div class='alert success'><i class='fas fa-check-circle'></i> Administrador eliminado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'><i class='fas fa-times-circle'></i> No se pudo eliminar. Solo se pueden eliminar admins normales.</div>";
        }
    }
}

// ── Obtener lista de admins normales ──────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id, usuario, nombre FROM administradores WHERE rol = 0 ORDER BY nombre ASC");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores — ITL</title>
    <link rel="stylesheet" href="estilos/estilos_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }

        .gestion-grid {
            display: grid;
            grid-template-columns: 1.3fr 0.7fr;
            gap: 28px;
            align-items: start;
        }
        @media (max-width: 860px) { .gestion-grid { grid-template-columns: 1fr; } }

        .panel-card {
            background: #fff; padding: 24px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07); border: 1px solid #eef2f5;
        }
        .panel-card h2 {
            font-size: 1.15rem; color: #111; margin: 0 0 18px 0;
            border-bottom: 2px solid #800020; padding-bottom: 10px;
            display: flex; align-items: center; gap: 10px;
        }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 11px 12px; text-align: left; color: #555; border-bottom: 2px solid #eee; font-size: .88rem; }
        td { padding: 11px 12px; border-bottom: 1px solid #eee; font-size: .9rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .txt-usuario { color: #800020; font-weight: bold; }

        .acciones-td { display: flex; gap: 7px; align-items: center; }

        /* Input contraseña inline */
        .inline-form { display: flex; gap: 7px; align-items: center; }
        .inline-form input[type="password"] {
            flex: 1; padding: 6px 9px; border: 1px solid #ccd1d9;
            border-radius: 5px; font-size: .84rem; min-width: 0;
        }
        .btn-key {
            background: #e67e22; color: white; border: none;
            padding: 7px 11px; border-radius: 5px; cursor: pointer;
            font-size: .85rem; white-space: nowrap; transition: background .2s;
        }
        .btn-key:hover { background: #d35400; }

        /* Botón baja */
        .btn-baja {
            background: #c0392b; color: white; border: none;
            padding: 7px 11px; border-radius: 5px; cursor: pointer;
            font-size: .85rem; transition: background .2s;
        }
        .btn-baja:hover { background: #922b21; }

        /* Formulario alta */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: .88rem; color: #444; }
        .form-group input {
            width: 100%; padding: 9px 11px; border: 1px solid #ccd1d9;
            border-radius: 6px; font-size: .93rem; box-sizing: border-box; transition: border-color .2s;
        }
        .form-group input:focus { outline: none; border-color: #800020; box-shadow: 0 0 0 3px rgba(128,0,32,.1); }
        .btn-form {
            background: #800020; color: white; border: none;
            padding: 11px 20px; border-radius: 6px; font-weight: bold;
            font-size: .97rem; cursor: pointer; width: 100%; transition: background .2s;
        }
        .btn-form:hover { background: #600018; }

        /* Alertas */
        .alert { padding: 12px 15px; margin-bottom: 22px; border-radius: 7px; font-weight: 500; font-size: .95rem; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error   { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        /* Header btn */
        .btn-volver {
            background: rgba(255,255,255,0.15); color: #fff; padding: 8px 16px;
            border-radius: 6px; text-decoration: none; font-weight: bold;
            font-size: .9rem; border: 1px solid rgba(255,255,255,0.25); transition: background .3s;
        }
        .btn-volver:hover { background: rgba(255,255,255,0.3); }

        .empty-msg { text-align: center; color: #999; padding: 20px 0; font-style: italic; }
    </style>
</head>
<body>

<header class="admin-header">
    <div class="header-content">
        <img src="img/logotipo.png" alt="Logo ITL" class="logo-min">
        <h1><i class="fas fa-users-cog"></i> Gestión de Administradores</h1>
        <div class="user-info">
            <span>
                <i class="fas fa-user-cog"></i>
                <?= htmlspecialchars($_SESSION['admin_nombre']) ?>
                <span style="background:#D4AF37;color:#000;font-size:0.7rem;font-weight:bold;padding:2px 8px;border-radius:4px;margin-left:6px;vertical-align:middle;">SUPERADMIN</span>
            </span>
            <a href="admin_dashboard.php" class="btn-volver" style="margin-right:8px;">
                <i class="fas fa-arrow-left"></i> Panel Alumnos
            </a>
            <a href="logout.php" class="btn-salir">Salir</a>
        </div>
    </div>
</header>

<main class="container">

    <?= $mensaje ?>

    <div class="gestion-grid">

        <!-- ── TABLA DE ADMINS ── -->
        <section class="panel-card">
            <h2><i class="fas fa-users"></i> Administradores Activos</h2>

            <?php if (empty($admins)): ?>
                <div class="empty-msg">No hay administradores registrados todavía.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Nueva contraseña</th>
                            <th>Baja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $ad): ?>
                        <tr>
                            <td><?= htmlspecialchars($ad['nombre']) ?></td>
                            <td class="txt-usuario">@<?= htmlspecialchars($ad['usuario']) ?></td>

                            <!-- Cambiar contraseña -->
                            <td>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="id_admin" value="<?= $ad['id'] ?>">
                                    <input type="password" name="nueva_password"
                                           placeholder="Nueva contraseña..." required
                                           autocomplete="new-password" minlength="6">
                                    <button type="submit" name="cambiar_password"
                                            class="btn-key" title="Guardar contraseña">
                                        <i class="fas fa-key"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- Dar de baja -->
                            <td>
                                <form method="POST"
                                      onsubmit="return confirm('¿Dar de baja a <?= htmlspecialchars($ad['nombre'], ENT_QUOTES) ?>? Esta acción no se puede deshacer.')">
                                    <input type="hidden" name="id_admin" value="<?= $ad['id'] ?>">
                                    <button type="submit" name="eliminar_admin" class="btn-baja" title="Dar de baja">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

        <!-- ── FORMULARIO ALTA ── -->
        <section class="panel-card">
            <h2><i class="fas fa-user-plus"></i> Registrar Nuevo Admin</h2>
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Nombre completo o área</label>
                    <input type="text" name="nombre" required placeholder="Ej. Laura García">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Nombre de usuario (login)</label>
                    <input type="text" name="usuario" required placeholder="Ej. laura_garcia"
                           pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guion bajo">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña inicial</label>
                    <input type="password" name="password" required
                           placeholder="Mínimo 6 caracteres" minlength="6">
                </div>
                <button type="submit" name="registrar_admin" class="btn-form">
                    <i class="fas fa-save"></i> Guardar Administrador
                </button>
            </form>
        </section>

    </div>
</main>

</body>
</html>
