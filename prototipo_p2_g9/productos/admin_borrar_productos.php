<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// 1. Seguridad: Solo el gerente puede hacer esto
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

// 2. Comprobar que venimos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $accion = $_POST['accion'] ?? '';

    if ($id && $accion) {
        if ($accion === 'retirar') {
            borraProducto($id);
            header('Location: ../admin/productos.php?status=deleted');
            exit;
        } elseif ($accion === 'restaurar') {
            restauraProducto($id);
            header('Location: ../admin/productos.php?status=restored');
            exit;
        }
    }
}

// Si falla algo o intentan entrar por URL directamente
header('Location: ../admin/productos.php');
exit;