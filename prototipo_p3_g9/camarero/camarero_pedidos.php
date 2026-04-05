<?php
require_once __DIR__ . '/../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\pedidos\Pedido;

if (!isset($_SESSION['login']) || !Usuario::tieneRol('camarero')) {
    header('Location: ' . RUTA_APP . '/login.php');
    exit();
}

$tituloPagina = 'Vista Camarero - Bistro FDI';

$user = Usuario::buscaUsuario($_SESSION['id']);
if ($user) {
    $avatarActual  = $user->getAvatar();
    $nombreUsuario = htmlspecialchars($user->getNombre());
} else {
    $avatarActual  = 'default.png';
    $nombreUsuario = 'Camarero';
}

if (strpos($avatarActual, 'predefinidos/') !== false) {
    $rutaAvatar = RUTA_IMGS . "avatares/" . $avatarActual;
} elseif ($avatarActual == 'default.png') {
    $rutaAvatar = RUTA_IMGS . "avatares/default.png";
} else {
    $rutaAvatar = RUTA_IMGS . "avatares/usuarios/" . $avatarActual;
}

$porCobrar   = Pedido::listaPedidosPorEstados(['recibido']);
$porTerminar = Pedido::listaPedidosPorEstados(['listo cocina']);
$porEntregar = Pedido::listaPedidosPorEstados(['terminado']);
$tipo_map    = ['local' => 'Local', 'llevar' => 'Para Llevar'];

function tarjetaPedido($pedido, $nuevo_estado, $texto_boton, $tipo_map) {
    $id_pedido     = $pedido->getId();
    $detalles      = Pedido::buscaDetallesPedido($id_pedido);
    $total         = number_format($pedido->getTotalIva(), 2);
    $tipo          = $tipo_map[$pedido->getTipo()] ?? $pedido->getTipo();
    $hora          = date('H:i', strtotime($pedido->getFechaHora()));
    $nombreCliente = htmlspecialchars($pedido->getNombreUsuario());
    $numDia        = htmlspecialchars($pedido->getNumeroDia());

    $productos = '';
    foreach ($detalles as $d) {
        $productos .= "<li>" . htmlspecialchars($d['nombre']) . " x{$d['cantidad']}</li>";
    }

    return "
    <div class='pedido-camarero-card'>
        <div class='pedido-camarero-header'>
            <strong class='pedido-camarero-num'>Pedido #{$numDia}</strong>
            <span class='pedido-camarero-tipo'>{$tipo}</span>
        </div>
        <div class='pedido-camarero-info'>Cliente: {$nombreCliente} &nbsp;|&nbsp; {$hora}</div>
        <ul class='pedido-camarero-lista'>{$productos}</ul>
        <div class='pedido-camarero-footer'>
            <strong>{$total} €</strong>
            <form method='POST' action='camarero_accion.php'>
                <input type='hidden' name='id_pedido'    value='{$id_pedido}'>
                <input type='hidden' name='nuevo_estado' value='{$nuevo_estado}'>
                <button type='submit' class='btn-oscuro btn-sm'>{$texto_boton}</button>
            </form>
        </div>
    </div>";
}

$contenidoPrincipal = "
<div class='camarero-bienvenida'>
    <img src='{$rutaAvatar}' alt='Mi avatar' class='camarero-avatar'>
    <div>
        <div class='camarero-nombre'>Hola, {$nombreUsuario} 👋</div>
        <div class='camarero-rol-label'>Vista Camarero</div>
    </div>
    <a href='" . RUTA_APP . "/index.php' class='nav-link btn-flotante-inicio'>← Inicio</a>
</div>

<h2 class='mt-0'>Por Cobrar
    <span class='h2-seccion-sub'>(pedidos recibidos, pendientes de pago)</span>
</h2>";

if (empty($porCobrar)) {
    $contenidoPrincipal .= "<p class='texto-gris-claro mb-30'>No hay pedidos pendientes de cobro.</p>";
} else {
    $contenidoPrincipal .= "<div class='grid-tarjetas'>";
    foreach ($porCobrar as $pedido) {
        $contenidoPrincipal .= tarjetaPedido($pedido, 'en preparación', 'Cobrado ✓', $tipo_map);
    }
    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= "
<h2>Por Revisar
    <span class='h2-seccion-sub'>(listos en cocina, pendientes de revisión del camarero)</span>
</h2>";

if (empty($porTerminar)) {
    $contenidoPrincipal .= "<p class='texto-gris-claro mb-30'>No hay pedidos listos en cocina.</p>";
} else {
    $contenidoPrincipal .= "<div class='grid-tarjetas'>";
    foreach ($porTerminar as $pedido) {
        $contenidoPrincipal .= tarjetaPedido($pedido, 'terminado', 'Revisado ✓', $tipo_map);
    }
    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= "
<h2>Por Entregar
    <span class='h2-seccion-sub'>(pedidos revisados, listos para el cliente)</span>
</h2>";

if (empty($porEntregar)) {
    $contenidoPrincipal .= "<p class='texto-gris-claro'>No hay pedidos listos para entregar.</p>";
} else {
    $contenidoPrincipal .= "<div class='grid-tarjetas'>";
    foreach ($porEntregar as $pedido) {
        $contenidoPrincipal .= tarjetaPedido($pedido, 'entregado', 'Entregado ✓', $tipo_map);
    }
    $contenidoPrincipal .= "</div>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
