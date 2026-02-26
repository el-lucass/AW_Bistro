<?php
require_once __DIR__ . '/mysql/bd.php';

// 1. Obtener todas las categorías (útil para el formulario de crear/editar producto)
function listaCategorias() {
    $conn = conectarBD();
    $result = $conn->query("SELECT * FROM categorias");
    
    $categorias = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
    }
    return $categorias;
}

// 2. Listar productos (con su imagen principal y categoría)
// Permite filtrar para ver todos (Admin) o solo los ofertados (Carta para clientes)
function listaProductos($solo_ofertados = true) {
    $conn = conectarBD();
    // Usamos una subconsulta para obtener solo la primera imagen insertada (MIN id) como imagen principal
    $sql = "SELECT p.*, c.nombre AS nombre_categoria, 
                   (SELECT ruta_imagen FROM producto_imagenes pi WHERE pi.id_producto = p.id ORDER BY id ASC LIMIT 1) AS imagen_principal
            FROM productos p
            JOIN categorias c ON p.id_categoria = c.id";
            
    if ($solo_ofertados) {
        $sql .= " WHERE p.ofertado = TRUE";
    }
    
    $result = $conn->query($sql);
    $productos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    return $productos;
}

// 3. Buscar un producto específico por ID (incluyendo todas sus imágenes)
function buscaProducto($id) {
    $conn = conectarBD();
    
    // Obtener datos básicos del producto
    $stmt = $conn->prepare("SELECT p.*, c.nombre AS nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();
    
    if ($producto) {
        // Obtener todas sus imágenes asociadas
        $stmt_img = $conn->prepare("SELECT id, ruta_imagen FROM producto_imagenes WHERE id_producto = ? ORDER BY id ASC");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $result_img = $stmt_img->get_result();
        
        $producto['imagenes'] = [];
        while ($img = $result_img->fetch_assoc()) {
            $producto['imagenes'][] = $img;
        }
    }
    
    return $producto;
}

// 4. Crear un producto nuevo
function creaProducto($nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes = []) {
    $conn = conectarBD();
    
    // Usamos transacciones para asegurar que se guarda el producto Y sus imágenes o ninguno
    $conn->begin_transaction(); 
    
    try {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado) VALUES (?, ?, ?, ?, ?, ?, TRUE)");
        $stmt->bind_param("ssidii", $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible);
        $stmt->execute();
        
        $id_producto = $conn->insert_id;
        
        // Insertar las imágenes si se han subido
        if (!empty($rutas_imagenes)) {
            $stmt_img = $conn->prepare("INSERT INTO producto_imagenes (id_producto, ruta_imagen) VALUES (?, ?)");
            foreach ($rutas_imagenes as $ruta) {
                $stmt_img->bind_param("is", $id_producto, $ruta);
                $stmt_img->execute();
            }
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// 5. Borrado Lógico (Retirar de la carta)
function borraProducto($id) {
    $conn = conectarBD();
    // No hacemos DELETE, solo cambiamos ofertado a FALSE según el enunciado
    $stmt = $conn->prepare("UPDATE productos SET ofertado = FALSE WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// 6. Restaurar producto (Volver a añadir a la carta)
function restauraProducto($id) {
    $conn = conectarBD();
    $stmt = $conn->prepare("UPDATE productos SET ofertado = TRUE WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// 7. Crear una nueva categoría
function creaCategoria($nombre, $descripcion) {
    $conn = conectarBD();
    $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);
    return $stmt->execute();
}

// 8. Actualizar un producto existente
function actualizaProducto($id, $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes = []) {
    $conn = conectarBD();
    $conn->begin_transaction(); 
    
    try {
        // Actualizamos los datos básicos
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, precio_base=?, iva=?, disponible=? WHERE id=?");
        $stmt->bind_param("ssidiii", $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $id);
        $stmt->execute();
        
        // Si hay imágenes nuevas, las añadimos
        if (!empty($rutas_imagenes)) {
            $stmt_img = $conn->prepare("INSERT INTO producto_imagenes (id_producto, ruta_imagen) VALUES (?, ?)");
            foreach ($rutas_imagenes as $ruta) {
                $stmt_img->bind_param("is", $id, $ruta);
                $stmt_img->execute();
            }
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// 9. Obtener información de una imagen específica
function buscaImagen($id_imagen) {
    $conn = conectarBD();
    $stmt = $conn->prepare("SELECT * FROM producto_imagenes WHERE id = ?");
    $stmt->bind_param("i", $id_imagen);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// 10. Borrar una imagen de la base de datos
function borraImagen($id_imagen) {
    $conn = conectarBD();
    $stmt = $conn->prepare("DELETE FROM producto_imagenes WHERE id = ?");
    $stmt->bind_param("i", $id_imagen);
    return $stmt->execute();
}

?>