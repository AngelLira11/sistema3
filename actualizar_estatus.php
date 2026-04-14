<?php
session_start();
require_once 'config.php';

// Verificación de seguridad
if (empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_alumno = $_POST['id_alumno'];
    $mencion = $_POST['mencion'];
    
    // Aquí podrías agregar más campos si decides guardar individualmente 
    // cada checkbox en la base de datos. Por ahora guardamos la mención.
    
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