<?php

//Para debug
// error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once __DIR__ . '/mysql/bd.php';

/**
 * Devuelve pedidos en preparación (aún no cogidos por cocinero)
 */
function listaPedidosEnPreparacion() {
    $conn = conectarBD();

    $sql = "SELECT p.*, u.nombre_usuario
            FROM pedidos p
            JOIN usuarios u ON u.id = p.id_usuario
            WHERE p.estado = 'en preparación'
              AND p.id_cocinero IS NULL
            ORDER BY p.fecha_hora ASC";

    $res = $conn->query($sql);
    $pedidos = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $pedidos[] = $row;
    }
    return $pedidos;
}

/**
 * Coge un pedido de forma segura (evita que dos cocineros lo cojan a la vez)
 * Devuelve true si lo cogió, false si ya no estaba disponible.
 */
function cogerPedido($id_pedido, $id_cocinero) {
    $conn = conectarBD();

    $sql = "UPDATE pedidos
            SET estado = 'cocinando', id_cocinero = ?
            WHERE id = ?
              AND estado = 'en preparación'
              AND id_cocinero IS NULL";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_cocinero, $id_pedido);
    $stmt->execute();

    return ($stmt->affected_rows === 1);
}

/**
 * Obtiene un pedido por ID
 */
function obtenerPedido($id_pedido) {
    $conn = conectarBD();
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Obtiene líneas del pedido con nombre de producto y flag preparado
 */
function obtenerLineasPedidoCocina($id_pedido) {
    $conn = conectarBD();

    $sql = "SELECT pp.id_pedido, pp.id_producto, pp.cantidad, pp.preparado, pp.precio_unitario_historico,
                   p.nombre
            FROM pedido_productos pp
            JOIN productos p ON p.id = pp.id_producto
            WHERE pp.id_pedido = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();

    $lineas = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $lineas[] = $row;

    return $lineas;
}

/**
 * Marca una línea como preparada
 */
function marcarProductoPreparado($id_pedido, $id_producto) {
    $conn = conectarBD();
    $stmt = $conn->prepare("UPDATE pedido_productos SET preparado = 1 WHERE id_pedido = ? AND id_producto = ?");
    $stmt->bind_param("ii", $id_pedido, $id_producto);
    return $stmt->execute();
}

/**
 * Devuelve cuántas líneas quedan pendientes (preparado=0)
 */
function pendientesPedido($id_pedido) {
    $conn = conectarBD();
    $stmt = $conn->prepare("SELECT COUNT(*) AS pendientes FROM pedido_productos WHERE id_pedido = ? AND preparado = 0");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)$row['pendientes'];
}

/**
 * Si no quedan pendientes y el pedido está cocinando -> pasa a listo cocina
 */
function pasarPedidoAListoCocinaSiProcede($id_pedido) {
    $pendientes = pendientesPedido($id_pedido);
    if ($pendientes !== 0) return false;

    $conn = conectarBD();
    $stmt = $conn->prepare("UPDATE pedidos SET estado = 'listo cocina' WHERE id = ? AND estado = 'cocinando'");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();

    return ($stmt->affected_rows === 1);
}

function listaMisPedidosCocinando($id_cocinero) {
    $conn = conectarBD();

    $sql = "SELECT p.*, u.nombre_usuario
            FROM pedidos p
            JOIN usuarios u ON u.id = p.id_usuario
            WHERE p.estado = 'cocinando'
              AND p.id_cocinero = ?
            ORDER BY p.fecha_hora ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cocinero);
    $stmt->execute();
    $res = $stmt->get_result();

    $pedidos = [];
    while ($row = $res->fetch_assoc()) $pedidos[] = $row;

    return $pedidos;
}


/**
 * GERENTE: lista pedidos pendientes con info de cliente y cocinero (avatar)
 */
function listaPedidosPendientesGerente() {
    $conn = conectarBD();

    $sql = "SELECT p.*,
                   uc.nombre_usuario AS cliente_user,
                   uc.nombre AS cliente_nombre,
                   uc.apellidos AS cliente_apellidos,
                   uco.nombre_usuario AS cocinero_user,
                   uco.avatar AS cocinero_avatar
            FROM pedidos p
            JOIN usuarios uc ON uc.id = p.id_usuario
            LEFT JOIN usuarios uco ON uco.id = p.id_cocinero
            WHERE p.estado IN ('recibido','en preparación','cocinando')
            ORDER BY p.fecha_hora ASC";

    $res = $conn->query($sql);

    $pedidos = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) $pedidos[] = $row;
    }
    return $pedidos;
}

/**
 * GERENTE: detalle de productos de un pedido (con preparado)
 */
function detallesPedidoGerente($id_pedido) {
    // Reutilizamos la misma query que cocina (incluye preparado)
    return obtenerLineasPedidoCocina($id_pedido);
}