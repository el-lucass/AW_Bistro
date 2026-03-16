<?php
require_once '../includes/config.php';
require_once '../includes/usuarios.php';

$nombre = $_POST['nombre_usuario'] ?? null;
$pass = $_POST['password'] ?? null;

if ($nombre && $pass) {
    $user = buscaUsuarioPorNombre($nombre);
    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['login'] = true;
        $_SESSION['nombre'] = $user['nombre_usuario'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['id'] = $user['id'];
        header('Location: ../index.php');
        exit;
    }
}
header('Location: ../login.php?error=1');