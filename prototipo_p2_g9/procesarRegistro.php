<?php
require_once 'includes/config.php';
require_once 'includes/mysql/bd.php';

// Capturamos los datos del formulario
$usuario = $_POST['nombre_usuario'] ?? null;
$password = $_POST['password'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$apellidos = $_POST['apellidos'] ?? null;
$email = $_POST['email'] ?? null;

if ($usuario && $password && $email) {
    $conn = conectarBD();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertamos incluyendo apellidos y el rol por defecto 'cliente' [cite: 51]
    $sql = "INSERT INTO usuarios (nombre_usuario, password, nombre, apellidos, email, rol) VALUES (?, ?, ?, ?, ?, 'cliente')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $usuario, $hash, $nombre, $apellidos, $email);

    if ($stmt->execute()) {
        header('Location: login.php'); // Si funciona, vamos al login
        exit;
    } else {
        echo "Error al registrar: " . $conn->error;
    }
} else {
    echo "Faltan campos obligatorios.";
}