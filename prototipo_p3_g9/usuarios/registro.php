<?php
require_once '../includes/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;

// Recogemos los datos del formulario
$user = $_POST['nombre_usuario'] ?? null;
$pass = $_POST['password'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$apellidos = $_POST['apellidos'] ?? null;
$email = $_POST['email'] ?? null;

if ($user && $pass && $email) {
    // 1. Verificamos si el usuario ya existe usando el Modelo
    if (buscaUsuarioPorNombre($user)) {
        // Redirigimos de vuelta con el error y el nombre que falló
        header('Location: ../registro.php?error=usuario_existe&intento=' . urlencode($user));
        exit;
    }

    // 2. Intentamos crear el usuario como 'cliente' por defecto
    if (creaUsuario($user, $pass, $nombre, $apellidos, $email)) {
        header('Location: ../login.php?status=registrado');
        exit;
    } else {
        header('Location: ../registro.php?error=error_db');
        exit;
    }
} else {
    // Error si faltan campos obligatorios
    header('Location: ../registro.php?error=campos_vacios');
    exit;
}