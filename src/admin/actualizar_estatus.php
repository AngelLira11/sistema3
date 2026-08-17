<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/csrf.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: ../../public/index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    $id_alumno = $_POST['id_alumno'];
    $mencion = $_POST['mencion'];
    
    
    $pdo = getConexion();
    $sql = "UPDATE alumnos SET mencion_honorifica = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$mencion, $id_alumno])) {
        // Regresa a la ficha con un mensaje de éxito
        header("Location: ../../public/ver_alumno.php?id=$id_alumno&status=success");
    } else {
        echo "Error al actualizar el expediente.";
    }
}
?>