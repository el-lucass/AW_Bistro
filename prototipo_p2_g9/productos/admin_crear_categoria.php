<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Producto;

// 1. Seguridad: Solo el gerente puede procesar la creación de categorías
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// 2. Comprobar que venimos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if (!empty($nombre) && !empty($descripcion)) {
        // LLAMADA ACTUALIZADA: Usamos el método estático de la clase Producto
        if (Producto::creaCategoria($nombre, $descripcion)) {
            // Éxito -> Al listado de categorías
            header('Location: ../admin/categorias.php?status=success');
            exit;
        } else {
            // Error de BD -> Al formulario con mensaje de error
            header('Location: ../admin/crear_categoria.php?error=db');
            exit;
        }
    } else {
        // Datos vacíos -> Al formulario
        header('Location: ../admin/crear_categoria.php?error=empty');
        exit;
    }
} else {
    // Si se accede directamente por URL, mandamos al formulario
    header('Location: ../admin/crear_categoria.php');
    exit;
}