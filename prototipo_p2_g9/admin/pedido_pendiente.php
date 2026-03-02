<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/cocina.php';

// Seguridad: solo admin
if (!isset($_SESSION['login']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../login.php');
    exit();
}

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pedido <= 0) {
    header("Location: pedidos_pendientes.php");
    exit();
}

$pedido = obtenerPedido($id_pedido);
if (!$pedido) {
    die("Pedido no encontrado");
}

$lineas = detallesPedidoGerente($id_pedido);

$tituloPagina = "Detalle Pedido #{$pedido['numero_dia']}";

$contenidoPrincipal = "<div style='max-width: 900px; margin: 0 auto; padding: 20px;'>

<h1>Pedido #".htmlspecialchars($pedido['numero_dia'])."</h1>
<p><strong>Estado:</strong> ".htmlspecialchars($pedido['estado'])."</p>

<a href='pedidos_pendientes.php'>
<button style='margin-bottom:20px; padding:8px 14px;'>← Volver</button>
</a>

<div style='border:1px solid #eee; background:#fff; border-radius:6px; padding:20px;'>";

if (empty($lineas)) {
    $contenidoPrincipal .= "<p>No hay productos.</p>";
} else {

    foreach ($lineas as $l) {

        $nombre = htmlspecialchars($l['nombre']);
        $cant = (int)$l['cantidad'];
        $prep = (int)$l['preparado'] === 1;

        $estadoProd = $prep ? "✅ Preparado" : "⏳ Pendiente";

        $contenidoPrincipal .= "
        <div style='display:flex; justify-content:space-between; border-bottom:1px solid #f2f2f2; padding:10px 0;'>
            <div>{$nombre} x{$cant}</div>
            <div>{$estadoProd}</div>
        </div>";
    }
}

$contenidoPrincipal .= "</div></div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';