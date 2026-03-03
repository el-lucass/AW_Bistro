<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/cocina.php';

// Seguridad: solo cocinero
if (!isset($_SESSION['login']) || $_SESSION['rol'] !== 'cocinero') {
    header('Location: login.php');
    exit();
}

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pedido <= 0) {
    header("Location: cocinero_pedidos.php");
    exit();
}

$pedido = obtenerPedido($id_pedido);
if (!$pedido) {
    http_response_code(404);
    die("Pedido no encontrado");
}

$idCocinero = (int)$_SESSION['id'];

// Si el pedido no es tuyo -> te echa
if ((int)$pedido['id_cocinero'] !== $idCocinero) {
    http_response_code(403);
    die("No puedes acceder a este pedido.");
}

// Si ya no está en cocinando, no tiene sentido quedarse aquí
if ($pedido['estado'] === 'listo cocina') {
    header("Location: cocinero_pedidos.php?msg=listo");
    exit();
}

if ($pedido['estado'] !== 'cocinando') {
    header("Location: cocinero_pedidos.php");
    exit();
}

$lineas = obtenerLineasPedidoCocina($id_pedido);
$pendientes = pendientesPedido($id_pedido);

$tituloPagina = "Cocina - Pedido #{$pedido['numero_dia']}";

$contenidoPrincipal = "<div style='max-width: 900px; margin: 0 auto; padding: 20px;'>
<div style='display:flex; justify-content:space-between; align-items:center; gap:15px;'>
  <div>
    <h1 style='margin:0;'>Pedido #".htmlspecialchars($pedido['numero_dia'])."</h1>
    <div style='color:#666; font-size:14px; margin-top:6px;'>Estado: <strong>Cocinando</strong> · Pendientes: <strong>{$pendientes}</strong></div>
  </div>
  <a href='cocinero_pedidos.php' style='text-decoration:none;'>
    <button style='padding:10px 14px; background:white; border:1px solid #ccc; cursor:pointer; border-radius:6px;'>← Volver</button>
  </a>
</div>

<div style='margin-top:20px; border:1px solid #eee; border-radius:6px; background:#fff; overflow:hidden;'>
  <div style='padding:14px; border-bottom:1px solid #eee; background:#fafafa; font-weight:bold;'>Productos</div>
  <div style='padding:14px;'>";

foreach ($lineas as $l) {
    $nombre = htmlspecialchars($l['nombre']);
    $cant = (int)$l['cantidad'];
    $prep = (int)$l['preparado'] === 1;

    $estadoTxt = $prep ? "✅ Preparado" : "⏳ Pendiente";

    $boton = $prep
        ? "<button disabled style='padding:8px 12px; background:#ddd; color:#555; border:none; border-radius:6px;'>Preparado</button>"
        : "
        <form method='POST' action='cocinero_accion.php' style='margin:0;'>
          <input type='hidden' name='accion' value='marcar_preparado'>
          <input type='hidden' name='id_pedido' value='{$id_pedido}'>
          <input type='hidden' name='id_producto' value='{$l['id_producto']}'>
          <button type='submit' style='padding:8px 12px; background:black; color:white; border:none; border-radius:6px; cursor:pointer;'>
            Marcar preparado
          </button>
        </form>";

    $contenidoPrincipal .= "
    <div style='display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #f3f3f3;'>
      <div>
        <div style='font-weight:600;'>{$nombre} <span style='color:#666; font-weight:400;'>x{$cant}</span></div>
        <div style='color:#666; font-size:13px;'>{$estadoTxt}</div>
      </div>
      <div>{$boton}</div>
    </div>";
}

if (empty($lineas)) {
    $contenidoPrincipal .= "<div style='color:#666;'>Este pedido no tiene líneas.</div>";
}

$contenidoPrincipal .= "
  </div>
</div>
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';