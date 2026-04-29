<?php
require_once __DIR__ . '/../includes/config.php';


use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\pedidos\Pedido;

if (!isset($_SESSION['login']) || !Usuario::tieneRol('camarero')) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

$id_pedido    = intval($_POST['id_pedido']    ?? 0);
$nuevo_estado = trim($_POST['nuevo_estado']   ?? '');

$transiciones_camarero = [
    'recibido'     => 'en preparación',
    'listo cocina' => 'terminado',
    'terminado'    => 'entregado',
];
$transiciones_gerente = $transiciones_camarero + [
    'recibido'       => 'cancelado',
    'en preparación' => 'cancelado',
];

$permitidas = Usuario::tieneRol('gerente') ? $transiciones_gerente : $transiciones_camarero;

if ($id_pedido > 0 && $nuevo_estado !== '') {
    
    $pedido = Pedido::buscaPedido($id_pedido);

    if ($pedido) {
        $estadoActual = $pedido->getEstado();
        
        if (isset($permitidas[$estadoActual]) && $permitidas[$estadoActual] === $nuevo_estado) {
            Pedido::actualizaEstadoPedido($id_pedido, $nuevo_estado);
        }
    }
}


header('Location: camarero_pedidos.php');
exit();
?>