<?php
session_start();
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/auth/csrf.php';

// PROTECCIÓN: Si no ha iniciado sesión o NO es SuperAdmin (rol != 1), lo saca de aquí
if (empty($_SESSION['admin_id']) || $_SESSION['admin_rol'] != 1) {
    header('Location: index.php'); 
    exit;
}

$pdo = getConexion();
$mensaje = "";

// 1. LÓGICA PARA DAR DE ALTA UN NUEVO ADMIN
if (isset($_POST['registrar_admin'])) {
    csrf_validate();
    $nuevo_usuario   = trim($_POST['usuario']);
    $password_plana  = trim($_POST['password']);
    $nombre_real     = trim($_POST['nombre']);

    if (!empty($nuevo_usuario) && !empty($password_plana) && !empty($nombre_real)) {
        // Encriptamos la contraseña
        $password_encriptada = password_hash($password_plana, PASSWORD_BCRYPT);
        
        // Se inserta con rol 0 (Admin normal)
        $sql = "INSERT INTO administradores (usuario, password, nombre, rol) VALUES (?, ?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nuevo_usuario, $password_encriptada, $nombre_real])) {
            $mensaje = "<div class='alert success'>¡Administrador '$nuevo_usuario' creado con éxito!</div>";
        } else {
            $mensaje = "<div class='alert error'>Error al crear el administrador.</div>";
        }
    }
}

// 2. LÓGICA PARA CAMBIAR CONTRASEÑA DE UN ADMIN
if (isset($_POST['cambiar_password'])) {
    csrf_validate();
    $admin_id       = $_POST['id_admin'];
    $nueva_pass_txt = trim($_POST['nueva_password']);

    if (!empty($admin_id) && !empty($nueva_pass_txt)) {
        $nueva_pass_encriptada = password_hash($nueva_pass_txt, PASSWORD_BCRYPT);
        
        $sql = "UPDATE administradores SET password = ? WHERE id = ? AND rol = 0";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nueva_pass_encriptada, $admin_id])) {
            $mensaje = "<div class='alert success'>Contraseña actualizada correctamente.</div>";
        } else {
            $mensaje = "<div class='alert error'>Error al actualizar la contraseña.</div>";
        }
    }
}

// 3. OBTENER LA LISTA DE ADMINISTRADORES (Excluyendo al SuperAdmin para que no se auto-modifique)
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
    <link rel="stylesheet" href="assets/css/estilos_admin.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- REESTRUCTURACIÓN DEL HEADER PARA EVITAR AMONTONAMIENTO --- */
        .admin-header .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px 20px;
        }

        .superadmin-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .superadmin-actions .btn-volver {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .superadmin-actions .btn-volver:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .superadmin-actions .btn-salir {
            color: #ffffff;
            text-decoration: underline;
            font-weight: 500;
        }

        /* --- CONTENEDOR PRINCIPAL Y GRID RESPONSIVO --- */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .gestion-grid {
            display: grid;
            grid-template-columns: 1fr; /* Por defecto 1 columna (Móvil) */
            gap: 30px;
            align-items: start;
        }

        /* Dispositivos medianos y grandes (Tabletas y Computadoras) */
        @media (min-width: 850px) {
            .gestion-grid {
                grid-template-columns: 1.2fr 0.8fr; /* La tabla toma un poco más de espacio que el formulario */
            }
        }

        /* --- CARDS MODERNAS --- */
        .panel-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border: 1px solid #eef2f5;
        }

        .panel-card h2 {
            font-size: 1.3rem;
            color: #111111;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #800020; /* Detalle guinda elegante */
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- FORMULARIOS --- */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: #444444;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccd1d9;
            border-radius: 6px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #800020;
            box-shadow: 0 0 0 3px rgba(128, 0, 32, 0.1);
        }

        .btn-form {
            background: #800020; /* Guinda institucional */
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s ease;
            box-shadow: 0 3px 6px rgba(128, 0, 32, 0.2);
        }

        .btn-form:hover {
            background: #600018;
        }

        /* --- TABLA OPTIMIZADA Y ACCIONES --- */
        .inline-form {
            display: flex;
            gap: 8px;
            align-items: center;
            width: 100%;
        }

        .inline-form input[type="password"] {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid #ccd1d9;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .btn-key {
            background: #e67e22;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-key:hover {
            background: #d35400;
        }

        /* --- ALERTAS --- */
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.95rem;
        }
        .success { background-color: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        /* Ajustes específicos para móviles */
        @media (max-width: 600px) {
            .admin-header h1 { font-size: 1.4rem; text-align: center; width: 100%; order: 3; }
            .superadmin-actions { width: 100%; justify-content: space-between; }
            .panel-card { padding: 15px; }
            .inline-form { flex-direction: column; align-items: stretch; }
            .inline-form input[type="password"] { width: 100%; }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="header-content">
            <img src="img/logotipo.png" alt="Logo ITL" class="logo-min">
            <h1>Panel SuperAdmin — Gestión de Personal</h1>
            
            <div class="superadmin-actions">
                <a href="admin_dashboard.php" class="btn-volver">
                    <i class="fas fa-arrow-left"></i> Volver al Panel
                </a>
                <a href="../src/auth/logout.php" class="btn-salir">Salir</a>
            </div>
        </div>
    </header>

    <main class="container">
        <?= $mensaje ?>

        <div class="gestion-grid">
            <section class="panel-card">
                <h2><i class="fas fa-users"></i> Administradores Activos</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Restablecer Contraseña</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($admins)): ?>
                                <tr><td colspan="3" style="text-align:center; color:#777;">No hay administradores registrados todavía.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($admins as $ad): ?>
                            <tr>
                                <td class="txt-bold" style="color: #800020;"><?= htmlspecialchars($ad['nombre']) ?></td>
                                <td><strong><?= htmlspecialchars($ad['usuario']) ?></strong></td>
                                <td>
                                    <form method="POST" class="inline-form">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="id_admin" value="<?= $ad['id'] ?>">
                                        <input type="password" name="nueva_password" placeholder="Nueva clave..." required autocomplete="new-password">
                                        <button type="submit" name="cambiar_password" class="btn-key" title="Actualizar contraseña">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel-card">
                <h2><i class="fas fa-user-plus"></i> Registrar Nuevo Administrador</h2>
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div class="form-group">
                        <label>Nombre Completo (Área / Persona):</label>
                        <input type="text" name="nombre" required placeholder="Ej: TecLaguna o Nombre Personal">
                    </div>
                    <div class="form-group">
                        <label>Nombre de Usuario (Login):</label>
                        <input type="text" name="usuario" required placeholder="Ej: admin_control">
                    </div>
                    <div class="form-group">
                        <label>Contraseña Inicial:</label>
                        <input type="password" name="password" required placeholder="Crea una contraseña segura">
                    </div>
                    <button type="submit" name="registrar_admin" class="btn-form">
                        <i class="fas fa-save"></i> Guardar Administrador
                    </button>
                </form>
            </section>
        </div>
    </main>
</body>
</body>
</html>