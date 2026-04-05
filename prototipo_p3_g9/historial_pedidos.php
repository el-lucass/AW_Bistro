<?php
require_once __DIR__.'/includes/config.php';

// Importamos la clase Pedido
use es\ucm\fdi\aw\pedidos\Pedido;

// Seguridad: solo clientes logueados
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

$tituloPagina = 'Historial de Pedidos - Bistro FDI';
$user_id = $_SESSION['id'];

$estado_map = [
    'nuevo'          => 'Nuevo',
    'recibido'       => 'Recibido',
    'en preparación' => 'En Preparación',
    'cocinando'      => 'Cocinando',
    'listo cocina'   => 'Listo en Cocina',
    'terminado'      => 'Terminado',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

$tipo_map = [
    'local'  => 'Consumir en Local',
    'llevar' => 'Para Llevar',
];

$clase_estado = [
    'recibido'       => 'estado-recibido',
    'en preparación' => 'estado-preparacion',
    'cocinando'      => 'estado-cocinando',
    'listo cocina'   => 'estado-listo',
    'terminado'      => 'estado-terminado',
    'cancelado'      => 'estado-cancelado',
    'entregado'      => 'estado-entregado',
];

$estados_activos = ['recibido', 'en preparación', 'cocinando', 'listo cocina', 'terminado'];

// --- OBTENER PEDIDOS USANDO LA CLASE ---

$pedidos         = Pedido::listaPedidosUsuario($user_id);
$pedidosActivos  = array_filter($pedidos, fn($p) => in_array($p->getEstado(), $estados_activos));
$pedidosHistorial = array_filter($pedidos, fn($p) => !in_array($p->getEstado(), $estados_activos));

// Helper para renderizar una tarjeta de pedido
$renderPedido = function($pedido) use (&$estado_map, &$tipo_map, &$clase_estado, $user_id) {
    $id_pedido    = $pedido->getId();
    $totalFormateado = number_format($pedido->getTotalIva(), 2);
    $fechaFormat  = date('j/n/Y, H:i:s', strtotime($pedido->getFechaHora()));
    $estado_raw   = $pedido->getEstado();
    $status_text  = $estado_map[$estado_raw] ?? ucfirst($estado_raw);
    $tipo_text    = $tipo_map[$pedido->getTipo()] ?? ucfirst($pedido->getTipo());
    $claseE       = $clase_estado[$estado_raw] ?? 'estado-default';

    $html = "
    <div class='pedido-hist-card'>
        <div class='pedido-hist-top'>
            <div>
                <div class='pedido-hist-num-fila'>
                    <h2 class='pedido-hist-num'>Pedido #{$pedido->getNumeroDia()}</h2>
                    <span class='badge-estado {$claseE}'>{$status_text}</span>
                </div>
                <div class='pedido-hist-fecha'>Fecha: {$fechaFormat}</div>
                <div class='pedido-hist-tipo'>Tipo: {$tipo_text}</div>
            </div>
            <div class='pedido-hist-total'>
                <div class='pedido-hist-total-val'>{$totalFormateado} €</div>
                <div class='pedido-hist-total-label'>Total</div>
                " . ($estado_raw === 'recibido' ? "
                <form method='POST' action='pedidos/cambiar_estado.php' class='mt-8'
                      onsubmit='return confirm(\"¿Cancelar el pedido #{$pedido->getNumeroDia()}?\")'>
                    <input type='hidden' name='id_pedido'    value='{$id_pedido}'>
                    <input type='hidden' name='nuevo_estado' value='cancelado'>
                    <input type='hidden' name='redirigir'    value='../historial_pedidos.php'>
                    <button type='submit' class='btn-peligro btn-sm'>Cancelar pedido</button>
                </form>" : "") . "
            </div>
        </div>
        <hr class='hr-sep'>
        <div class='pedido-hist-prods-titulo'>Productos</div>
        <div class='mb-10'>";

    $detalles = Pedido::buscaDetallesPedido($id_pedido);
    foreach ($detalles as $detalle) {
        $subtotalLine = number_format($detalle['precio_unitario_historico'] * $detalle['cantidad'], 2);
        $html .= "
            <div class='pedido-hist-prod-linea'>
                <span>" . htmlspecialchars($detalle['nombre']) . " x{$detalle['cantidad']}</span>
                <span>{$subtotalLine} €</span>
            </div>";
    }

    $html .= "</div></div>";
    return $html;
};

$contenidoPrincipal = "
<div class='mb-20'>
    <a href='index.php' class='nav-link'>← Volver al inicio</a>
</div>
<h1 class='mt-0 mb-30'>Historial de Pedidos</h1>";

if (empty($pedidos)) {
    $contenidoPrincipal .= "
    <div class='historial-vacio'>
        <p class='texto-lg'>Aún no has realizado ningún pedido.</p>
        <a href='catalogo.php'>
            <button class='btn-oscuro btn-lg mt-20'>¡Empieza a pedir ahora!</button>
        </a>
    </div>";
} else {
    if (!empty($pedidosActivos)) {
        $contenidoPrincipal .= "<h2 class='seccion-activos'>Pedidos en curso</h2>";
        foreach ($pedidosActivos as $pedido) {
            $contenidoPrincipal .= $renderPedido($pedido);
        }
    }
    if (!empty($pedidosHistorial)) {
        $contenidoPrincipal .= "<h2 class='seccion-historial'>Historial</h2>";
        foreach ($pedidosHistorial as $pedido) {
            $contenidoPrincipal .= $renderPedido($pedido);
        }
    }
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
