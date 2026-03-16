<?php
require_once 'includes/config.php';

// Importamos la clase Pedido
use es\ucm\fdi\aw\Pedido;

// Seguridad: solo clientes logueados
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

$tituloPagina = 'Historial de Pedidos - Bistro FDI';
$user_id = $_SESSION['id']; 

$contenidoPrincipal = "<div style='padding: 20px; max-width: 800px; margin: 0 auto;'>";

$estiloBotonVolver = "text-decoration: none; color: #333; background-color: white; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; display: inline-block;";

$contenidoPrincipal .= "
<div style='margin-bottom: 20px;'>
    <a href='index.php' style='{$estiloBotonVolver}'>
        ← Volver al inicio
    </a>
</div>
<h1 style='margin-top: 0; margin-bottom: 30px; font-size: 24px;'>Historial de Pedidos</h1>
";

// Mapa de estados para visualización
$estado_map = [
    'nuevo' => 'Nuevo',
    'recibido' => 'Recibido',
    'en preparación' => 'En Preparación',
    'cocinando' => 'Cocinando',
    'listo cocina' => 'Listo en Cocina',
    'terminado' => 'Terminado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado'
];

$tipo_map = [
    'local' => 'Consumir en Local',
    'llevar' => 'Para Llevar'
];

$estados_activos = ['recibido', 'en preparación', 'cocinando', 'listo cocina', 'terminado'];

// --- OBTENER PEDIDOS USANDO LA CLASE ---
$pedidos = Pedido::listaPedidosUsuario($user_id);

$pedidosActivos   = array_filter($pedidos, fn($p) => in_array($p->getEstado(), $estados_activos));
$pedidosHistorial = array_filter($pedidos, fn($p) => !in_array($p->getEstado(), $estados_activos));

$colores_estado = [
    'recibido'       => ['bg' => '#fff3cd', 'color' => '#856404'],
    'en preparación' => ['bg' => '#cce5ff', 'color' => '#004085'],
    'cocinando'      => ['bg' => '#d4edda', 'color' => '#155724'],
    'listo cocina'   => ['bg' => '#d1ecf1', 'color' => '#0c5460'],
    'terminado'      => ['bg' => '#e2d9f3', 'color' => '#4a235a'],
];

// Helper para renderizar una tarjeta de pedido
$renderPedido = function($pedido) use (&$estado_map, &$tipo_map, &$colores_estado, $user_id) {
    $id_pedido = $pedido->getId();
    $totalFormateado = number_format($pedido->getTotalIva(), 2);
    $fechaFormat = date('j/n/Y, H:i:s', strtotime($pedido->getFechaHora()));
    $estado_raw = $pedido->getEstado();
    $status_text = isset($estado_map[$estado_raw]) ? $estado_map[$estado_raw] : ucfirst($estado_raw);
    $tipo_raw = $pedido->getTipo();
    $tipo_text = isset($tipo_map[$tipo_raw]) ? $tipo_map[$tipo_raw] : ucfirst($tipo_raw);
    $badgeBg    = $colores_estado[$estado_raw]['bg']    ?? '#e2e8f0';
    $badgeColor = $colores_estado[$estado_raw]['color'] ?? '#4a5568';

    $html = "
    <div style='border: 1px solid #ddd; padding: 25px; margin-bottom: 25px; background-color: #fff;'>
        <div style='display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;'>
            <div>
                <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 10px;'>
                    <h2 style='margin: 0; font-size: 20px;'>Pedido #{$pedido->getNumeroDia()}</h2>
                    <span style='background-color: {$badgeBg}; color: {$badgeColor}; padding: 4px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;'>
                        {$status_text}
                    </span>
                </div>
                <div style='color: #666; font-size: 13px; margin-bottom: 5px;'>Fecha: {$fechaFormat}</div>
                <div style='color: #666; font-size: 13px;'>Tipo: {$tipo_text}</div>
            </div>
            <div style='text-align: right;'>
                <div style='font-size: 20px; font-weight: bold;'>{$totalFormateado} €</div>
                <div style='color: #666; font-size: 11px;'>Total</div>
                " . ($estado_raw === 'recibido' ? "
                <form method='POST' action='pedidos/cambiar_estado.php' style='margin-top:8px;'
                      onsubmit='return confirm(\"¿Cancelar el pedido #{$pedido->getNumeroDia()}?\")'>
                    <input type='hidden' name='id_pedido'    value='{$id_pedido}'>
                    <input type='hidden' name='nuevo_estado' value='cancelado'>
                    <input type='hidden' name='redirigir'    value='../historial_pedidos.php'>
                    <button type='submit'
                        style='padding:5px 12px; background:#dc3545; color:white; border:none; cursor:pointer; font-size:12px;'>
                        Cancelar pedido
                    </button>
                </form>" : "") . "
            </div>
        </div>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
        <div style='font-weight: bold; margin-bottom: 15px; font-size: 14px;'>Productos</div>
        <div style='margin-bottom: 10px;'>";

    $detalles = Pedido::buscaDetallesPedido($id_pedido);
    foreach ($detalles as $detalle) {
        $subtotalLine = number_format($detalle['precio_unitario_historico'] * $detalle['cantidad'], 2);
        $html .= "
            <div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'>
                <span>" . htmlspecialchars($detalle['nombre']) . " x{$detalle['cantidad']}</span>
                <span>{$subtotalLine} €</span>
            </div>";
    }

    $html .= "
        </div>
    </div>";
    return $html;
};

if (empty($pedidos)) {
    $contenidoPrincipal .= "
    <div style='text-align: center; color: #666; margin-top: 50px;'>
        <p style='font-size: 18px;'>Aún no has realizado ningún pedido.</p>
        <a href='catalogo.php' style='text-decoration: none;'>
            <button style='padding: 10px 20px; background: black; color: white; border: none; cursor: pointer; border-radius: 5px; margin-top: 20px;'>
                ¡Empieza a pedir ahora!
            </button>
        </a>
    </div>";
} else {
    // --- SECCIÓN PEDIDOS EN CURSO ---
    if (!empty($pedidosActivos)) {
        $contenidoPrincipal .= "
        <h2 style='font-size: 20px; margin-bottom: 15px; border-left: 4px solid #f59e0b; padding-left: 10px;'>
            Pedidos en curso
        </h2>";
        foreach ($pedidosActivos as $pedido) {
            $contenidoPrincipal .= $renderPedido($pedido);
        }
    }

    // --- SECCIÓN HISTORIAL ---
    if (!empty($pedidosHistorial)) {
        $contenidoPrincipal .= "
        <h2 style='font-size: 20px; margin-bottom: 15px; margin-top: 30px; border-left: 4px solid #9ca3af; padding-left: 10px;'>
            Historial
        </h2>";
        foreach ($pedidosHistorial as $pedido) {
            $contenidoPrincipal .= $renderPedido($pedido);
        }
    }

}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';