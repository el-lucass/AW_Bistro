<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// 1. Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

// 2. Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    // Validar que no estén vacíos
    if (!empty($nombre) && !empty($descripcion)) {
        if (creaCategoria($nombre, $descripcion)) {
            header('Location: ../admin/categorias.php?status=success');
            exit;
        } else {
            header('Location: ../admin/categorias.php?error=db');
            exit;
        }
    } else {
        header('Location: ../admin/categorias.php?error=empty');
        exit;
    }
} else {
    header('Location: ../admin/categorias.php');
    exit;
}