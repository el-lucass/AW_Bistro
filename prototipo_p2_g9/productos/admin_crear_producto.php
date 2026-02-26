<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// 1. Seguridad: Solo el gerente puede procesar esto
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
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
    // El checkbox 'disponible' solo llega en POST si está marcado
    $disponible = isset($_POST['disponible']) ? 1 : 0; 
    
    // 3. Procesar las imágenes (Múltiples archivos)
    $rutas_imagenes = [];
    $directorio_destino = '../img/productos/'; 
    
    // Verifica que haya llegado al menos un archivo
    if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
        $total_archivos = count($_FILES['imagenes']['name']);
        
        for ($i = 0; $i < $total_archivos; $i++) {
            $nombre_original = $_FILES['imagenes']['name'][$i];
            $tmp_name = $_FILES['imagenes']['tmp_name'][$i];
            $error = $_FILES['imagenes']['error'][$i];
            
            // Si no hubo error al subir este archivo concreto
            if ($error === UPLOAD_ERR_OK) {
                // Generamos un nombre único (timestamp + uniqid) para evitar que dos fotos se llamen "foto1.jpg"
                $nombre_unico = time() . "_" . uniqid() . "_" . basename($nombre_original);
                $ruta_final = $directorio_destino . $nombre_unico;
                
                // Movemos el archivo temporal a nuestra carpeta final
                if (move_uploaded_file($tmp_name, $ruta_final)) {
                    // Solo guardamos en el array el nombre del archivo para la BD
                    $rutas_imagenes[] = $nombre_unico;
                }
            }
        }
    }
    
    // 4. Llamar al Modelo para guardar en la Base de Datos
    if (creaProducto($nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes)) {
        // Todo fue bien. Redirigimos de vuelta al formulario con mensaje de éxito.
        header('Location: ../admin/productos.php?status=created');
        exit;
    } else {
        // Error al guardar en la base de datos
        header('Location: ../admin/crear_producto.php?error=db');
        exit;
    }

} else {
    // Si alguien intenta acceder a la URL directamente sin mandar datos, lo devolvemos
    header('Location: ../admin/crear_producto.php');
    exit;
}