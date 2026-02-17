<?php
require_once 'includes/config.php';
require_once 'includes/mysql/bd.php';

$usuario = $_POST['nombre_usuario'] ?? null;
$password = $_POST['password'] ?? null;

if ($usuario && $password) {
    $conn = conectarBD();
    $stmt = $conn->prepare("SELECT id, nombre_usuario, password, rol FROM usuarios WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['login'] = true;
        $_SESSION['nombre'] = $user['nombre_usuario'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['id'] = $user['id'];
        header('Location: index.php');
    } else {
        echo "Login incorrecto. <a href='login.php'>Reintentar</a>";
    }
}