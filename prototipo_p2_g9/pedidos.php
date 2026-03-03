<?php
require_once 'includes/config.php';
require_once 'includes/pedidos.php';
require_once 'includes/usuarios.php';

// Solo el gerente (y roles superiores)
if (!isset($_SESSION['login']) || !tieneRol('gerente')) {
    header('Location: login.php');
    exit();
}

$tituloPagina = 'Gestión de Pedidos - Bistro FDI';

$pedidos = listaTodosLosPedidosActivos();

$estado_map = [
    'nuevo'          => 'Nuevo',
    'recibido'       => 'Recibido',
    'en preparación' => 'En Preparación',
    'cocinando'      => 'Cocinando',
    'listo cocina'   => 'Listo Cocina',
    'terminado'      => 'Terminado',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

$tipo_map = ['local' => 'Local', 'llevar' => 'Para Llevar'];

// Colores de fondo por estado
$color_map = [
    'nuevo'          => '#f8f9fa',
    'recibido'       => '#fff3cd',
    'en preparación' => '#cfe2ff',
    'cocinando'      => '#d1ecf1',
    'listo cocina'   => '#d4edda',
    'terminado'      => '#e2d9f3',
    'entregado'      => '#f8f9fa',
    'cancelado'      => '#f8d7da',
];

$contenidoPrincipal = "
<div style='padding:20px; max-width:1100px; margin:0 auto;'>
    <div style='display:flex; align-items:center; gap:15px; margin-bottom:30px;'>
        <h1 style='margin:0;'>Gestión de Pedidos</h1>
        <a href='index.php' style='margin-left:auto; text-decoration:none;'>
            <button style='padding:8px 15px; background:white; border:1px solid #bbb; cursor:pointer; font-size:13px;'>
                ← Inicio
            </button>
        </a>
    </div>";

if (empty($pedidos)) {
    $contenidoPrincipal .= "<p style='color:#888;'>No hay pedidos activos en este momento.</p>";
} else {
    $contenidoPrincipal .= "
    <table style='width:100%; border-collapse:collapse; font-size:14px;'>
        <thead>
            <tr style='background:#f1f1f1;'>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>#</th>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>Cliente</th>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>Hora</th>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>Tipo</th>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>Total</th>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>Estado</th>
                <th style='padding:10px; text-align:left; border-bottom:2px solid #ddd;'>Acción</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($pedidos as $pedido) {
        $estado_txt = $estado_map[$pedido['estado']] ?? ucfirst($pedido['estado']);
        $tipo_txt   = $tipo_map[$pedido['tipo']]     ?? $pedido['tipo'];
        $hora       = date('H:i', strtotime($pedido['fecha_hora']));
        $total      = number_format($pedido['total_iva'], 2);
        $bg         = $color_map[$pedido['estado']] ?? '#fff';

        // Botón de cancelar solo en recibido
        $boton_cancelar = '';
        if ($pedido['estado'] === 'recibido') {
            $boton_cancelar = "
            <form method='POST' action='pedidos/cambiar_estado.php' style='display:inline;'
                  onsubmit='return confirm(\"¿Cancelar el pedido #{$pedido['numero_dia']}?\")'>
                <input type='hidden' name='id_pedido'    value='{$pedido['id']}'>
                <input type='hidden' name='nuevo_estado' value='cancelado'>
                <input type='hidden' name='redirigir'    value='../pedidos.php'>
                <button type='submit'
                    style='padding:5px 10px; background:#dc3545; color:white; border:none; cursor:pointer; font-size:12px;'>
                    Cancelar
                </button>
            </form>";
        }

        $contenidoPrincipal .= "
        <tr style='background:{$bg}; border-bottom:1px solid #eee;'>
            <td style='padding:10px;'><strong>#{$pedido['numero_dia']}</strong></td>
            <td style='padding:10px;'>{$pedido['nombre_usuario']}</td>
            <td style='padding:10px;'>{$hora}</td>
            <td style='padding:10px;'>{$tipo_txt}</td>
            <td style='padding:10px;'>{$total} €</td>
            <td style='padding:10px;'><span style='font-weight:bold;'>{$estado_txt}</span></td>
            <td style='padding:10px;'>{$boton_cancelar}</td>
        </tr>";
    }

    $contenidoPrincipal .= "</tbody></table>";
}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
