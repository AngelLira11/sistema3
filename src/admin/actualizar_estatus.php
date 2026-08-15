<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_alumno = $_POST['id_alumno'];
    $mencion = $_POST['mencion'];
    
    
    $pdo = getConexion();
    $sql = "UPDATE alumnos SET mencion_honorifica = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$mencion, $id_alumno])) {
        // Regresa a la ficha con un mensaje de éxito
        header("Location: ver_alumno.php?id=$id_alumno&status=success");
    } else {
        echo "Error al actualizar el expediente.";
    }
}
?>