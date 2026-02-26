<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if (!empty($nombre) && !empty($descripcion)) {
        if (creaCategoria($nombre, $descripcion)) {
            // Éxito -> Al listado de categorías
            header('Location: ../admin/categorias.php?status=success');
            exit;
        } else {
            // Error de BD -> Al formulario
            header('Location: ../admin/crear_categoria.php?error=db');
            exit;
        }
    } else {
        // Datos vacíos -> Al formulario
        header('Location: ../admin/crear_categoria.php?error=empty');
        exit;
    }
} else {
    header('Location: ../admin/crear_categoria.php');
    exit;
}