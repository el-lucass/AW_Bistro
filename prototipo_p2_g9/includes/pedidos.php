<?php
require_once __DIR__ . '/mysql/bd.php';


// 1. Crear un pedido nuevo con sus productos (Usamos transacción para asegurar que todo se guarda o nada)
function creaPedido($id_usuario, $tipo, $total_iva, $carrito, $estado = 'recibido') {
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

        // 1.2 Insertar el pedido en la tabla principal con el estado correcto
        $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, numero_dia, tipo, total_iva, estado) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisds", $id_usuario, $numero_dia, $tipo, $total_iva, $estado);
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

// 3. Actualizar el estado de un pedido
function actualizaEstadoPedido($id_pedido, $nuevo_estado) {
    $conn = conectarBD();
    $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);
    return $stmt->execute();
}

// 4. Obtener pedidos filtrados por uno o varios estados (con datos del usuario)
function listaPedidosPorEstados($estados) {
    $conn = conectarBD();
    $placeholders = implode(',', array_fill(0, count($estados), '?'));
    $types = str_repeat('s', count($estados));
    $stmt = $conn->prepare(
        "SELECT p.*, u.nombre_usuario, u.avatar
         FROM pedidos p
         JOIN usuarios u ON p.id_usuario = u.id
         WHERE p.estado IN ($placeholders)
         ORDER BY p.fecha_hora ASC"
    );
    $stmt->bind_param($types, ...$estados);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// 5. Obtener todos los pedidos activos (para el gerente)lui
function listaTodosLosPedidosActivos() {
    $conn = conectarBD();
    $stmt = $conn->prepare(
        "SELECT p.*, u.nombre_usuario
         FROM pedidos p
         JOIN usuarios u ON p.id_usuario = u.id
         WHERE p.estado NOT IN ('entregado', 'cancelado')
         ORDER BY p.fecha_hora ASC"
    );
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// 6. Obtener un pedido concreto con los datos del usuario
function buscaPedido($id_pedido) {
    $conn = conectarBD();
    $stmt = $conn->prepare(
        "SELECT p.*, u.nombre_usuario
         FROM pedidos p
         JOIN usuarios u ON p.id_usuario = u.id
         WHERE p.id = ?"
    );
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// 7. Obtener los detalles (productos) de un pedido concreto
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