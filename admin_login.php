<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Administrativo — ITL</title>
    <link rel="stylesheet" href="estilos/estilos_registro.css"> </head>
<body>
<div id="padre">
    <div id="hijo">
        <h1>Admin — Titulación</h1>
        
        <?php if(isset($_GET['error'])): ?>
            <p style="color:red;">Usuario o contraseña incorrectos.</p>
        <?php endif; ?>

        <form action="admin_login_process.php" method="POST">
            <div class="campo">
                <label>Usuario:</label>
                <input type="text" name="usuario" required placeholder="Ej. jose_admin">
            </div>
            <div class="campo">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Entrar al Panel</button>
        </form>
        <br>
        <a href="index.php">Volver al inicio de alumnos</a>
    </div>
</div>
</body>
</html>