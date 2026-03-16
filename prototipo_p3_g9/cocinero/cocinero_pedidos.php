<?php
require_once __DIR__ . '/../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\cocineros\Cocina;

// Seguridad: solo cocinero
if (!Usuario::tieneRol('cocinero')) {
    header('Location: ../login.php');
    exit();
}

$idCocinero = (int)$_SESSION['id'];

// Llamadas estáticas
$misPedidos = Cocina::listaMisPedidosCocinando($idCocinero);
$pedidos = Cocina::listaPedidosEnPreparacion();

$tituloPagina = 'Cocina - Bistro FDI';

$msg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'no_disponible') {
    $msg = "<p style='color:#b00; margin:0 0 15px 0;'>Ese pedido ya no está disponible (otro cocinero lo cogió o cambió el estado).</p>";
}
if (isset($_GET['msg']) && $_GET['msg'] === 'listo') {
    $msg = "<p style='color:green; margin:0 0 15px 0;'>✅ Pedido terminado.</p>";
}

$contenidoPrincipal = "<div style='max-width: 900px; margin: 0 auto; padding: 20px;'>
<h1 style='margin-top:0;'>Cocina</h1>
{$msg}";

/** =======================
 * 1) MIS PEDIDOS EN CURSO
 * ======================= */
$contenidoPrincipal .= "
<h2 style='margin-top:25px; margin-bottom:10px;'>Mis pedidos en curso</h2>
<div style='border:1px solid #eee; border-radius:6px; overflow:hidden; background:#fff; margin-bottom:25px;'>";

if (empty($misPedidos)) {
    $contenidoPrincipal .= "<div style='padding:20px; color:#666;'>No tienes pedidos en curso.</div>";
} else {
    $contenidoPrincipal .= "
    <table style='width:100%; border-collapse:collapse; font-size:14px;'>
      <thead>
        <tr style='background:#fafafa; border-bottom:1px solid #eee;'>
          <th style='text-align:left; padding:12px;'>#</th>
          <th style='text-align:left; padding:12px;'>Fecha</th>
          <th style='text-align:left; padding:12px;'>Tipo</th>
          <th style='text-align:left; padding:12px;'>Cliente</th>
          <th style='text-align:center; padding:12px;'>Ir</th>
        </tr>
      </thead>
      <tbody>";

    foreach ($misPedidos as $p) {
        $num = htmlspecialchars($p['numero_dia']);
        $fecha = date('d/m/Y H:i', strtotime($p['fecha_hora']));
        $tipo = ($p['tipo'] === 'local') ? 'Local' : 'Para llevar';
        $cliente = htmlspecialchars($p['nombre_usuario']);

        $contenidoPrincipal .= "
        <tr style='border-bottom:1px solid #f2f2f2;'>
          <td style='padding:12px;'>#{$num}</td>
          <td style='padding:12px;'>{$fecha}</td>
          <td style='padding:12px;'>{$tipo}</td>
          <td style='padding:12px;'>{$cliente}</td>
          <td style='padding:12px; text-align:center;'>
            <a href='cocinero_pedido.php?id={$p['id']}' style='text-decoration:none;'>
              <button style='padding:8px 12px; background:black; color:white; border:none; cursor:pointer; border-radius:5px;'>
                Ver
              </button>
            </a>
          </td>
        </tr>";
    }

    $contenidoPrincipal .= "</tbody></table>";
}

$contenidoPrincipal .= "</div>";

/** =======================
 * 2) PEDIDOS EN PREPARACIÓN
 * ======================= */
$contenidoPrincipal .= "
<h2 style='margin-top:10px; margin-bottom:10px;'>Pedidos en preparación (Sin asignar)</h2>
<div style='border:1px solid #eee; border-radius:6px; overflow:hidden; background:#fff;'>";

if (empty($pedidos)) {
    $contenidoPrincipal .= "<div style='padding:20px; color:#666;'>No hay pedidos en preparación ahora mismo.</div>";
} else {
    $contenidoPrincipal .= "
    <table style='width:100%; border-collapse:collapse; font-size:14px;'>
      <thead>
        <tr style='background:#fafafa; border-bottom:1px solid #eee;'>
          <th style='text-align:left; padding:12px;'>#</th>
          <th style='text-align:left; padding:12px;'>Fecha</th>
          <th style='text-align:left; padding:12px;'>Tipo</th>
          <th style='text-align:left; padding:12px;'>Cliente</th>
          <th style='text-align:right; padding:12px;'>Total</th>
          <th style='text-align:center; padding:12px;'>Acción</th>
        </tr>
      </thead>
      <tbody>";

    foreach ($pedidos as $p) {
        $num = htmlspecialchars($p['numero_dia']);
        $fecha = date('d/m/Y H:i', strtotime($p['fecha_hora']));
        $tipo = ($p['tipo'] === 'local') ? 'Local' : 'Para llevar';
        $cliente = htmlspecialchars($p['nombre_usuario']);
        $total = number_format((float)$p['total_iva'], 2);

        $contenidoPrincipal .= "
        <tr style='border-bottom:1px solid #f2f2f2;'>
          <td style='padding:12px;'>#{$num}</td>
          <td style='padding:12px;'>{$fecha}</td>
          <td style='padding:12px;'>{$tipo}</td>
          <td style='padding:12px;'>{$cliente}</td>
          <td style='padding:12px; text-align:right;'>{$total} €</td>
          <td style='padding:12px; text-align:center;'>
            <form method='POST' action='cocinero_accion.php' style='margin:0;'>
              <input type='hidden' name='accion' value='coger_pedido'>
              <input type='hidden' name='id_pedido' value='{$p['id']}'>
              <button type='submit' style='padding:8px 12px; background:black; color:white; border:none; cursor:pointer; border-radius:5px;'>
                Cocinar
              </button>
            </form>
          </td>
        </tr>";
    }

    $contenidoPrincipal .= "</tbody></table>";
}

$contenidoPrincipal .= "</div>";

// Cierre del contenedor principal
$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';