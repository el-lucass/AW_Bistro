<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\incidencias\Incidencia;

// 1. Seguridad: Solo el gerente puede hacer esto
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// 2. Comprobar que venimos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = $_POST['id_pedido'] ?? null;
    $accion = $_POST['accion'] ?? '';

    if ($id_pedido && $accion) {
        Incidencia::cambiarEstado($id_pedido, $accion);
        header("Location: ./incidencias.php");
        exit;
    }
}

// Si falla algo o intentan entrar copiando la URL directamente
header('Location: ./incidencias.php');
exit;