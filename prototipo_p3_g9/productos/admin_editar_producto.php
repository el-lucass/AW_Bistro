<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Producto;

// 1. Seguridad: Solo el gerente puede procesar esto
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $id_categoria = $_POST['id_categoria'] ?? 0;
    $precio_base = $_POST['precio_base'] ?? 0;
    $iva = $_POST['iva'] ?? 21;
    $disponible = isset($_POST['disponible']) ? 1 : 0; 
    
    if (!$id) {
        header('Location: ../admin/productos.php');
        exit;
    }

    // 2. Procesar imágenes nuevas (opcional)
    $rutas_imagenes = [];
    $directorio_destino = '../img/productos/'; 
    
    if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
        $total_archivos = count($_FILES['imagenes']['name']);
        for ($i = 0; $i < $total_archivos; $i++) {
            $tmp_name = $_FILES['imagenes']['tmp_name'][$i];
            $error = $_FILES['imagenes']['error'][$i];
            
            if ($error === UPLOAD_ERR_OK) {
                $nombre_unico = time() . "_" . uniqid() . "_" . basename($_FILES['imagenes']['name'][$i]);
                if (move_uploaded_file($tmp_name, $directorio_destino . $nombre_unico)) {
                    $rutas_imagenes[] = $nombre_unico;
                }
            }
        }
    }

    // 3. Procesar eliminación de imágenes (checkboxes) usando métodos estáticos
    if (isset($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
        foreach ($_POST['eliminar_imagenes'] as $id_img_borrar) {
            // 1. Obtener los datos de la imagen (Clase Producto)
            $img_data = Producto::buscaImagen($id_img_borrar);
            
            if ($img_data) {
                // 2. Borrar el archivo físico
                $ruta_fisica = '../img/productos/' . $img_data['ruta_imagen'];
                if (file_exists($ruta_fisica)) {
                    @unlink($ruta_fisica);
                }
                
                // 3. Borrar el registro de la base de datos
                Producto::borraImagen($id_img_borrar);
            }
        }
    }
    
    // 4. Llamar al modelo mediante la clase estática
    if (Producto::actualizaProducto($id, $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes)) {
        header('Location: ../admin/productos.php?status=updated');
        exit;
    } else {
        header("Location: ../admin/editar_producto.php?id=$id&error=db");
        exit;
    }

} else {
    header('Location: ../admin/productos.php');
    exit;
}