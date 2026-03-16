<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Producto;

// 1. Seguridad: Solo el gerente puede hacer esto
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// 2. Comprobar que venimos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $accion = $_POST['accion'] ?? '';

    if ($id && $accion) {
        if ($accion === 'retirar') {
            // Llamamos al método estático de la clase Producto
            Producto::borraProducto($id);
            header("Location: ../admin/productos.php?status=deleted#fila-producto-$id");
            exit;
        } elseif ($accion === 'restaurar') {
            // Llamamos al método estático de la clase Producto
            Producto::restauraProducto($id);
            header("Location: ../admin/productos.php?status=restored#fila-producto-$id");
            exit;
        }
    }
}

// Si falla algo o intentan entrar copiando la URL directamente
header('Location: ../admin/productos.php');
exit;