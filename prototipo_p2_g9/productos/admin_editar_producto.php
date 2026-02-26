<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// 1. Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
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

    // 3. Procesar eliminacion de imagenes exteranas (checkboxes)
    if (isset($_POST['eliminar_imagenes']) && is_array($_POST['eliminar_imagenes'])) {
        foreach ($_POST['eliminar_imagenes'] as $id_img_borrar) {
            // 1. Obtener los datos de la imagen para saber su nombre de archivo
            $img_data = buscaImagen($id_img_borrar);
            
            if ($img_data) {
                // 2. Borrar el archivo físico de la carpeta img/productos/
                $ruta_fisica = '../img/productos/' . $img_data['ruta_imagen'];
                if (file_exists($ruta_fisica)) {
                    @unlink($ruta_fisica); // @ evita warnings si el archivo no existe
                }
                
                // 3. Borrar el registro de la base de datos
                borraImagen($id_img_borrar);
            }
        }
    }
    
    // 3. Llamar al modelo
    if (actualizaProducto($id, $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes)) {
        // Redirigimos al panel de productos con éxito
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