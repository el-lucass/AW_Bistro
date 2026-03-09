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

// --- OBTENER PEDIDOS USANDO LA CLASE ---
$pedidos = Pedido::listaPedidosUsuario($user_id);

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
    // --- BUCLE DE PEDIDOS (Objetos Pedido) ---
    foreach ($pedidos as $pedido) {
        $id_pedido = $pedido->getId();
        $totalFormateado = number_format($pedido->getTotalIva(), 2);
        $fechaFormat = date('j/n/Y, H:i:s', strtotime($pedido->getFechaHora()));
        
        $estado_raw = $pedido->getEstado();
        $status_text = isset($estado_map[$estado_raw]) ? $estado_map[$estado_raw] : ucfirst($estado_raw);
        
        $tipo_raw = $pedido->getTipo();
        $tipo_text = isset($tipo_map[$tipo_raw]) ? $tipo_map[$tipo_raw] : ucfirst($tipo_raw);

        $contenidoPrincipal .= "
        <div style='border: 1px solid #ddd; padding: 25px; margin-bottom: 25px; background-color: #fff;'>
            
            <div style='display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;'>
                <div>
                    <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 10px;'>
                        <h2 style='margin: 0; font-size: 20px;'>Pedido #{$pedido->getNumeroDia()}</h2>
                        <span style='background-color: #e2e8f0; color: #4a5568; padding: 4px 10px; border-radius: 3px; font-size: 12px; font-weight: bold;'>
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

            <div style='margin-bottom: 10px;'>
        ";

        // --- OBTENER DETALLES (Array asociativo) ---
        $detalles = Pedido::buscaDetallesPedido($id_pedido);
        
        foreach ($detalles as $detalle) {
            $subtotalLine = number_format($detalle['precio_unitario_historico'] * $detalle['cantidad'], 2);
            $contenidoPrincipal .= "
                <div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'>
                    <span>" . htmlspecialchars($detalle['nombre']) . " x{$detalle['cantidad']}</span>
                    <span>{$subtotalLine} €</span>
                </div>
            ";
        }

        $contenidoPrincipal .= "
            </div>
        </div> ";
    }
}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';