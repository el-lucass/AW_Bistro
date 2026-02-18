<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

$usuario = $_POST['nombre_usuario'] ?? null;
$password = $_POST['password'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$apellidos = $_POST['apellidos'] ?? null;
$email = $_POST['email'] ?? null;

if ($usuario && $password && $email) {
    $conn = conectarBD();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $sql = "INSERT INTO usuarios (nombre_usuario, password, nombre, apellidos, email, rol) VALUES (?, ?, ?, ?, ?, 'cliente')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $usuario, $hash, $nombre, $apellidos, $email);
        $stmt->execute();

        // Si llega aquí, todo ha ido bien
        header('Location: ../login.php');
        exit;

    } catch (mysqli_sql_exception $e) {
        // El código 1062 es el error de "Duplicate entry" en MySQL
        if ($e->getCode() == 1062) {
            echo "<h2>Error</h2>";
            echo "<p>Lo sentimos, el nombre de usuario <strong>'$usuario'</strong> ya está en uso. Por favor, elige otro.</p>";
            echo "<a href='../registro.php'>Volver al registro</a>";
        } else {
            // Si es otro error distinto, lo mostramos
            echo "Error inesperado: " . $e->getMessage();
        }
    }
} else {
    echo "Faltan campos obligatorios.";
}