<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// 1. Seguridad: Solo el gerente puede hacer esto
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

// 2. Comprobar que venimos por POST desde los botones de la tabla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $accion = $_POST['accion'] ?? '';

    if ($id && $accion) {
        if ($accion === 'retirar') {
            borraProducto($id);
            // Añadimos el ancla al final de la URL
            header("Location: ../admin/productos.php?status=deleted#fila-producto-$id");
            exit;
        } elseif ($accion === 'restaurar') {
            restauraProducto($id);
            // Añadimos el ancla al final de la URL
            header("Location: ../admin/productos.php?status=restored#fila-producto-$id");
            exit;
        }
    }
}

// Si falla algo o intentan entrar copiando la URL directamente
header('Location: ../admin/productos.php');
exit;