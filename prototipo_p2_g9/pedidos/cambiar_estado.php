<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/pedidos.php';
require_once __DIR__ . '/../includes/usuarios.php';

// Solo personal logueado puede cambiar estados
if (!isset($_SESSION['login']) || !tieneRol('camarero')) {
    header('Location: ../index.php');
    exit();
}

$id_pedido   = intval($_POST['id_pedido']   ?? 0);
$nuevo_estado = trim($_POST['nuevo_estado'] ?? '');
$redirigir   = $_POST['redirigir']          ?? '../index.php';

// Transiciones permitidas por rol
$transiciones_camarero = [
    'recibido'     => 'en preparación',
    'listo cocina' => 'terminado',
    'terminado'    => 'entregado',
];
$transiciones_gerente = $transiciones_camarero + [
    'recibido'      => 'cancelado',
    'en preparación' => 'cancelado',
];

$permitidas = tieneRol('gerente') ? $transiciones_gerente : $transiciones_camarero;

$pedido = buscaPedido($id_pedido);

if ($pedido && isset($permitidas[$pedido['estado']]) && $permitidas[$pedido['estado']] === $nuevo_estado) {
    actualizaEstadoPedido($id_pedido, $nuevo_estado);
}

header('Location: ' . $redirigir);
exit();
?>
