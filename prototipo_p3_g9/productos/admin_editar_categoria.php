<?php
require_once '../includes/config.php';

// Importamos las clases necesarias para que el Autoloader sepa qué buscar
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Producto;

// Seguridad: Solo el gerente puede procesar cambios en categorías
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validamos que haya datos
    if ($id && !empty($nombre) && !empty($descripcion)) {
        
        // LLAMADA ACTUALIZADA: Usamos el método estático de la clase Producto
        if (Producto::actualizaCategoria($id, $nombre, $descripcion)) {
            // Éxito -> Volvemos a la tabla de categorías
            header('Location: ../admin/categorias.php?status=updated');
            exit;
        } else {
            // Error en BD
            header("Location: ../admin/editar_categoria.php?id=$id&error=db");
            exit;
        }
    } else {
        // Datos vacíos
        header("Location: ../admin/editar_categoria.php?id=$id&error=empty");
        exit;
    }
} else {
    // Si se intenta acceder sin POST, redirigimos a la tabla
    header('Location: ../admin/categorias.php');
    exit;
}