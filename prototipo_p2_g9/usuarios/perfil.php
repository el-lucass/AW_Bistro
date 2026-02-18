<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['login'])) {
    $conn = conectarBD();
    $id = $_SESSION['id'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        // Si el usuario puso una clave nueva, la encriptamos
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre=?, apellidos=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nombre, $apellidos, $email, $hash, $id);
    } else {
        // Si no, actualizamos solo el resto
        $sql = "UPDATE usuarios SET nombre=?, apellidos=?, email=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $apellidos, $email, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['nombre_real'] = $nombre; // Actualizamos el saludo si quieres
        header('Location: ../perfil.php?msg=ok');
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}