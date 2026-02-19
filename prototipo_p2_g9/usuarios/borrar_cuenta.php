<?php
require_once '../includes/config.php';
require_once '../includes/usuarios.php';

if (isset($_SESSION['login'])) {
    $id = $_SESSION['id'];
    $usuario = buscaUsuario($id);

    if (borraUsuario($id)) {
        // Limpieza de avatar personalizado si existe
        if ($usuario && !str_contains($usuario['avatar'], 'predefinidos/') && $usuario['avatar'] !== 'default.png') {
            @unlink("../img/avatares/usuarios/" . $usuario['avatar']);
        }
        session_destroy();
        header('Location: ' . RUTA_APP . '/index.php');
        exit;
    }
}