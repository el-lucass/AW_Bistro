<?php
require_once '../includes/config.php';

// Importamos las clases necesarias del namespace
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

// 1. Seguridad: Solo el gerente puede procesar esto (usando el método estático de Usuario)
if (!Usuario::tieneRol('gerente')) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit;
}

// 2. Comprobar que venimos de un formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recoger los datos básicos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $id_categoria = $_POST['id_categoria'] ?? 0;
    $precio_base = $_POST['precio_base'] ?? 0;
    $iva = $_POST['iva'] ?? 21;
    $disponible = isset($_POST['disponible']) ? 1 : 0; 
    
    // 3. Procesar las imágenes (Múltiples archivos)
    $rutas_imagenes = [];
    $directorio_destino = '../img/productos/'; 
    
    if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
        $total_archivos = count($_FILES['imagenes']['name']);
        
        for ($i = 0; $i < $total_archivos; $i++) {
            $nombre_original = $_FILES['imagenes']['name'][$i];
            $tmp_name = $_FILES['imagenes']['tmp_name'][$i];
            $error = $_FILES['imagenes']['error'][$i];
            
            if ($error === UPLOAD_ERR_OK) {
                // Nombre único para evitar colisiones
                $nombre_unico = time() . "_" . uniqid() . "_" . basename($nombre_original);
                $ruta_final = $directorio_destino . $nombre_unico;
                
                if (move_uploaded_file($tmp_name, $ruta_final)) {
                    $rutas_imagenes[] = $nombre_unico;
                }
            }
        }
    }
    
    // 4. Llamar al Modelo (Clase Producto) para guardar en la BD
    // Este método ya maneja la transacción interna
    if (Producto::creaProducto($nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes)) {
        header('Location: ../admin/productos.php?status=created');
        exit;
    } else {
        header('Location: ../admin/crear_producto.php?error=db');
        exit;
    }

} else {
    header('Location: ../admin/crear_producto.php');
    exit;
}