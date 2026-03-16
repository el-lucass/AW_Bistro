<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;

// 1. Seguridad: Solo el gerente puede crear usuarios
if (!tieneRol('gerente')) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['nombre_usuario'] ?? null;
    $pass = $_POST['password'] ?? null;
    $rol = $_POST['rol'] ?? 'cliente';

    // 2. Validación: ¿Ya existe este nombre?
    if (buscaUsuarioPorNombre($user)) {
        // Redirigimos de vuelta a la VISTA de creación con el error
        header('Location: ../admin/crear_usuario.php?error=usuario_existe&intento=' . urlencode($user));
        exit;
    }

    // 3. Si no existe, procedemos a crear
    if (creaUsuario($user, $pass, $_POST['nombre'], $_POST['apellidos'], $_POST['email'], $rol)) {
        header('Location: ../admin/usuarios.php?msg=creado');
        exit;
    } else {
        header('Location: ../admin/crear_usuario.php?error=db');
        exit;
    }
}