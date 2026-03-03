<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/cocina.php';

// Seguridad: solo cocinero
if (!isset($_SESSION['login']) || $_SESSION['rol'] !== 'cocinero') {
    header('Location: login.php');
    exit();
}

$accion = $_POST['accion'] ?? '';
$idCocinero = (int)$_SESSION['id'];

if ($accion === 'coger_pedido') {

    $idPedido = isset($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : 0;
    if ($idPedido <= 0) {
        header("Location: cocinero_pedidos.php");
        exit();
    }

    $ok = cogerPedido($idPedido, $idCocinero);
    if ($ok) {
        header("Location: cocinero_pedido.php?id=" . $idPedido);
        exit();
    } else {
        header("Location: cocinero_pedidos.php?msg=no_disponible");
        exit();
    }

} elseif ($accion === 'marcar_preparado') {

    $idPedido = isset($_POST['id_pedido']) ? (int)$_POST['id_pedido'] : 0;
    $idProducto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;

    if ($idPedido <= 0 || $idProducto <= 0) {
        header("Location: cocinero_pedidos.php");
        exit();
    }

    // Seguridad extra: comprobar que el pedido es de este cocinero y está en cocinando
    $pedido = obtenerPedido($idPedido);
    if (!$pedido || $pedido['estado'] !== 'cocinando' || (int)$pedido['id_cocinero'] !== $idCocinero) {
        http_response_code(403);
        die("Acción no permitida.");
    }

    marcarProductoPreparado($idPedido, $idProducto);

    // Si ya está todo, pasamos a listo cocina
    pasarPedidoAListoCocinaSiProcede($idPedido);

    header("Location: cocinero_pedido.php?id=" . $idPedido);
    exit();

} else {
    header("Location: cocinero_pedidos.php");
    exit();
}