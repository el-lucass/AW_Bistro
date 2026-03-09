<?php
require_once __DIR__ . '/../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Pedido;

// Solo personal logueado puede cambiar estados
if (!isset($_SESSION['login']) || !Usuario::tieneRol('camarero')) {
    header('Location: ' . RUTA_APP . '/index.php');
    exit();
}

$id_pedido    = intval($_POST['id_pedido']    ?? 0);
$nuevo_estado = trim($_POST['nuevo_estado']   ?? '');

// Transiciones permitidas por rol
$transiciones_camarero = [
    'recibido'     => 'en preparación',
    'listo cocina' => 'entregado',
];
$transiciones_gerente = $transiciones_camarero + [
    'recibido'       => 'cancelado',
    'en preparación' => 'cancelado',
];

$permitidas = Usuario::tieneRol('gerente') ? $transiciones_gerente : $transiciones_camarero;

if ($id_pedido > 0 && $nuevo_estado !== '') {
    // Usamos el método estático que devuelve un OBJETO Pedido
    $pedido = Pedido::buscaPedido($id_pedido);

    if ($pedido) {
        $estadoActual = $pedido->getEstado();
        
        if (isset($permitidas[$estadoActual]) && $permitidas[$estadoActual] === $nuevo_estado) {
            Pedido::actualizaEstadoPedido($id_pedido, $nuevo_estado);
        }
    }
}

// Volvemos a la vista del camarero
header('Location: camarero_pedidos.php');
exit();
?>