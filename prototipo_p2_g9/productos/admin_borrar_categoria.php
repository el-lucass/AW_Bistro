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
    
    if ($id) {
        if (borraCategoria($id)) {
            header('Location: ../admin/categorias.php?status=deleted');
            exit;
        } else {
            header('Location: ../admin/categorias.php?error=db');
            exit;
        }
    }
}
header('Location: ../admin/categorias.php');
exit;