<?php
require_once 'includes/config.php';
require_once 'includes/pedidos.php';

// Seguridad: solo clientes logueados
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

$tituloPagina = 'Historial de Pedidos - Bistro FDI';
$user_id = $_SESSION['id']; // Obtenemos el ID del cliente logueado

// 1. ELIMINAMOS font-family PARA QUE HEREDE LA FUENTE DE TU PLANTILLA
$contenidoPrincipal = "<div style='padding: 20px; max-width: 800px; margin: 0 auto;'>";

// Estilo para el botón de volver (coherente con el resto de la web)
$estiloBotonVolver = "text-decoration: none; color: #333; background-color: white; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; display: inline-block;";

// 2. CAMBIAMOS EL TEXTO A BOTÓN Y APUNTAMOS AL INICIO (index.php)
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

// --- OBTENER PEDIDOS DEL USUARIO ---
$pedidos = listaPedidosUsuario($user_id);

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
    // --- BUCLE DE PEDIDOS ---
    foreach ($pedidos as $pedido) {
        $totalFormateado = number_format($pedido['total_iva'], 2);
        // Formateamos la fecha: J/N/Y, H:i:s para que quede como en la imagen
        $fechaFormat = date('j/n/Y, H:i:s', strtotime($pedido['fecha_hora']));
        $status_text = isset($estado_map[$pedido['estado']]) ? $estado_map[$pedido['estado']] : ucfirst($pedido['estado']);
        $tipo_text = isset($tipo_map[$pedido['tipo']]) ? $tipo_map[$pedido['tipo']] : ucfirst($pedido['tipo']);

        $contenidoPrincipal .= "
        <div style='border: 1px solid #ddd; padding: 25px; margin-bottom: 25px; background-color: #fff;'>
            
            <div style='display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;'>
                <div>
                    <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 10px;'>
                        <h2 style='margin: 0; font-size: 20px;'>Pedido #{$pedido['numero_dia']}</h2>
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
                </div>
            </div>

            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>

            <div style='font-weight: bold; margin-bottom: 15px; font-size: 14px;'>Productos</div>

            <div style='margin-bottom: 10px;'>
        ";

        // --- OBTENER DETALLES DE PRODUCTOS DE ESTE PEDIDO ---
        $detalles = buscaDetallesPedido($pedido['id']);
        
        foreach ($detalles as $detalle) {
            $subtotalLine = number_format($detalle['precio_unitario_historico'] * $detalle['cantidad'], 2);
            $contenidoPrincipal .= "
                <div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'>
                    <span>{$detalle['nombre']} x{$detalle['cantidad']}</span>
                    <span>{$subtotalLine} €</span>
                </div>
            ";
        }

        $contenidoPrincipal .= "
            </div>
        </div> ";
    }
}

$contenidoPrincipal .= "</div>"; // Cierre contenedor principal

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>