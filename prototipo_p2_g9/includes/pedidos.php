<?php
require_once __DIR__ . '/mysql/bd.php';


// 1. Crear un pedido nuevo con sus productos (Usamos transacción para asegurar que todo se guarda o nada)
function creaPedido($id_usuario, $tipo, $total_iva, $carrito) {
    $conn = conectarBD();
    $conn->begin_transaction(); 
    
    try {
        // 1.1 Calcular el número de pedido del día (Empezando por 1 cada día nuevo)
        $sql_num = "SELECT MAX(numero_dia) as max_dia FROM pedidos WHERE DATE(fecha_hora) = CURDATE()";
        $result_num = $conn->query($sql_num);
        $row_num = $result_num->fetch_assoc();
        
        $numero_dia = 1; // Por defecto
        if ($row_num['max_dia'] !== null) {
            $numero_dia = $row_num['max_dia'] + 1; // Le sumamos 1 al máximo de hoy
        }

        // 1.2 Insertar el pedido en la tabla principal
        // El estado 'nuevo' y la fecha_hora (CURRENT_TIMESTAMP) se ponen solos según tu tabla
        $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, numero_dia, tipo, total_iva) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisd", $id_usuario, $numero_dia, $tipo, $total_iva);
        $stmt->execute();
        
        // Recuperamos el ID autoincremental que nos acaba de generar
        $id_pedido = $conn->insert_id;
        
        // 1.3 Insertar cada línea de producto en la tabla pedido_productos
        $stmt_prod = $conn->prepare("INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario_historico) VALUES (?, ?, ?, ?)");
        
        // ¡Cambiado para adaptarse a tu array!
        foreach ($carrito['productos'] as $item) {
            $id_producto = $item['id_producto']; // Lo sacamos de dentro del item
            $cantidad = $item['cantidad'];
            $precio = $item['precio']; 
            $stmt_prod->bind_param("iiid", $id_pedido, $id_producto, $cantidad, $precio);
            $stmt_prod->execute();
        }
        
        // 1.4 Obtener la fecha_hora exacta que guardó la BD para mostrarla en el ticket
        $stmt_fecha = $conn->prepare("SELECT fecha_hora FROM pedidos WHERE id = ?");
        $stmt_fecha->bind_param("i", $id_pedido);
        $stmt_fecha->execute();
        $row_fecha = $stmt_fecha->get_result()->fetch_assoc();
        
        // Si todo ha ido bien, confirmamos los cambios
        $conn->commit();
        
        // Devolvemos los datos necesarios para pintar el ticket
        return [
            'exito' => true,
            'id_pedido' => $id_pedido,
            'numero_dia' => $numero_dia,
            'fecha_hora' => $row_fecha['fecha_hora']
        ];
        
    } catch (Exception $e) {
        // Si algo falla, deshacemos todos los inserts
        $conn->rollback();
        return ['exito' => false];
    }
}

// 2. Obtener el historial de pedidos de un usuario concreto
function listaPedidosUsuario($id_usuario) {
    $conn = conectarBD();
    
    // Buscamos los pedidos del usuario y los ordenamos por fecha descendente (los más nuevos primero)
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_hora DESC");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pedidos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
    }
    return $pedidos;
}

// 3. Obtener los detalles (productos) de un pedido concreto
function buscaDetallesPedido($id_pedido) {
    $conn = conectarBD();
    
    // Hacemos un JOIN con la tabla productos para traernos también el nombre del producto
    $sql = "SELECT pp.*, p.nombre 
            FROM pedido_productos pp 
            JOIN productos p ON pp.id_producto = p.id 
            WHERE pp.id_pedido = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $detalles = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
    }
    return $detalles;
}

?>