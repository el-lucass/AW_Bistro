<?php
require_once __DIR__ . '/../includes/config.php';

// Importamos las clases
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\cocinero\Cocina;

// Seguridad: solo admin usando el método de Usuario
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../login.php');
    exit();
}

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pedido <= 0) {
    header("Location: pedidos_pendientes.php");
    exit();
}

// LLAMADA ESTÁTICA: Usamos la clase Cocina
$pedido = Cocina::obtenerPedido($id_pedido);
if (!$pedido) {
    die("Pedido no encontrado");
}

// LLAMADA ESTÁTICA: Usamos la clase Cocina
$lineas = Cocina::detallesPedidoGerente($id_pedido);
$tituloPagina = "Detalle Pedido #{$pedido['numero_dia']}";

$contenidoPrincipal = "
<h1>Pedido #" . htmlspecialchars($pedido['numero_dia']) . "</h1>
<p><strong>Estado:</strong> " . htmlspecialchars($pedido['estado']) . "</p>
<a href='pedidos_pendientes.php'>
    <button class='btn-contorno btn-lg mb-20'>← Volver</button>
</a>
<div class='panel-tabla'>";

if (empty($lineas)) {
    $contenidoPrincipal .= "<div class='panel-vacio'>No hay productos.</div>";
} else {
    foreach ($lineas as $l) {
        $nombre    = htmlspecialchars($l['nombre']);
        $cant      = (int)$l['cantidad'];
        $estadoProd = ((int)$l['preparado'] === 1) ? "✅ Preparado" : "⏳ Pendiente";
        $contenidoPrincipal .= "
        <div class='flex-entre fila-linea-pedido'>
            <div>{$nombre} x{$cant}</div>
            <div>{$estadoProd}</div>
        </div>";
    }
}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';