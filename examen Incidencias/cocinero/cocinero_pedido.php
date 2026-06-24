<?php
require_once __DIR__ . '/../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\cocineros\Cocina;

if (!Usuario::tieneRol('cocinero')) {
    header('Location: ../login.php');
    exit();
}

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pedido <= 0) {
    header("Location: cocinero_pedidos.php");
    exit();
}

$pedido = Cocina::obtenerPedido($id_pedido);
if (!$pedido) {
    http_response_code(404);
    die("Pedido no encontrado");
}

$idCocinero = (int)$_SESSION['id'];

if ((int)$pedido['id_cocinero'] !== $idCocinero) {
    http_response_code(403);
    die("No puedes acceder a este pedido.");
}

if ($pedido['estado'] === 'listo cocina') {
    header("Location: cocinero_pedidos.php?msg=listo");
    exit();
}

if ($pedido['estado'] !== 'cocinando') {
    header("Location: cocinero_pedidos.php");
    exit();
}

$lineas = Cocina::obtenerLineasPedidoCocina($id_pedido);
$pendientes = Cocina::pendientesPedido($id_pedido);

$tituloPagina = "Cocina - Pedido #{$pedido['numero_dia']}";

$contenidoPrincipal = "
<div class='cocina-header'>
  <div>
    <h1 class='mb-0'>Pedido #" . htmlspecialchars($pedido['numero_dia']) . "</h1>
    <div class='cocina-estado-info'>Estado: <strong>Cocinando</strong> · Pendientes: <strong>{$pendientes}</strong></div>
  </div>
  <a href='cocinero_pedidos.php' class='nav-link'>← Volver</a>
</div>

<div class='cocina-productos-box'>
  <div class='cocina-productos-titulo'>Productos</div>
  <div class='cocina-productos-body'>";

foreach ($lineas as $l) {
    $nombre = htmlspecialchars($l['nombre']);
    $cant   = (int)$l['cantidad'];
    $prep   = (int)$l['preparado'] === 1;

    $estadoTxt = $prep ? "Preparado" : "Pendiente";

    $boton = $prep
        ? "<button disabled class='btn-disabled'>Preparado</button>"
        : "
        <form method='POST' action='cocinero_accion.php' class='inline'>
          <input type='hidden' name='accion'      value='marcar_preparado'>
          <input type='hidden' name='id_pedido'   value='{$id_pedido}'>
          <input type='hidden' name='id_producto' value='{$l['id_producto']}'>
          <button type='submit' class='btn-oscuro btn-sm'>Marcar preparado</button>
        </form>";

    $contenidoPrincipal .= "
    <div class='cocina-linea'>
      <div>
        <div class='cocina-linea-info'>{$nombre} <span class='cocina-linea-cant'>x{$cant}</span></div>
        <div class='cocina-linea-estado'>{$estadoTxt}</div>
      </div>
      <div>{$boton}</div>
    </div>";
}

if (empty($lineas)) {
    $contenidoPrincipal .= "<p class='texto-gris'>Este pedido no tiene líneas.</p>";
}

$contenidoPrincipal .= "
  </div>
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
