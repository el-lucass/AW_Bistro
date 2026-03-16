<?php
require_once '../includes/config.php';

// Importamos las clases necesarias para el Autoloader
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

// 1. Seguridad: Solo el gerente puede borrar categorías
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// 2. Comprobar que venimos por POST (seguridad frente a borrados accidentales por URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        // LLAMADA ACTUALIZADA: Usamos el método estático de la clase Producto
        if (Producto::borraCategoria($id)) {
            header('Location: ../admin/categorias.php?status=deleted');
            exit;
        } else {
            header('Location: ../admin/categorias.php?error=db');
            exit;
        }
    }
}

// 3. Si no hay ID o no es POST, volvemos al listado
header('Location: ../admin/categorias.php');
exit;