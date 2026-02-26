<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Validamos que haya datos
    if ($id && !empty($nombre) && !empty($descripcion)) {
        if (actualizaCategoria($id, $nombre, $descripcion)) {
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
    header('Location: ../admin/categorias.php');
    exit;
}