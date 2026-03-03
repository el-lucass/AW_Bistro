<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/pedidos.php';
require_once __DIR__ . '/../includes/usuarios.php';
require_once __DIR__ . '/../includes/mysql/bd.php';

// Solo camareros (y roles superiores) pueden acceder
if (!isset($_SESSION['login']) || !tieneRol('camarero')) {
    header('Location: ' . RUTA_APP . '/login.php');
    exit();
}

$tituloPagina = 'Vista Camarero - Bistro FDI';

// Obtener datos del camarero para mostrar su avatar
$conn = conectarBD();
$user = $conn->query("SELECT * FROM usuarios WHERE id = '" . intval($_SESSION['id']) . "'")->fetch_assoc();

$avatarActual = $user['avatar'];
if (strpos($avatarActual, 'predefinidos/') !== false) {
    $rutaAvatar = RUTA_IMGS . "avatares/" . $avatarActual;
} elseif ($avatarActual == 'default.png') {
    $rutaAvatar = RUTA_IMGS . "avatares/default.png";
} else {
    $rutaAvatar = RUTA_IMGS . "avatares/usuarios/" . $avatarActual;
}

// Pedidos por cobrar (estado recibido) y por entregar (listos en cocina)
$porCobrar   = listaPedidosPorEstados(['recibido']);
$porEntregar = listaPedidosPorEstados(['listo cocina']);

$tipo_map = ['local' => 'Local', 'llevar' => 'Para Llevar'];

// ── Helper: pintar una tarjeta de pedido con un botón de acción ──────────────
function tarjetaPedido($pedido, $nuevo_estado, $texto_boton, $tipo_map) {
    $detalles  = buscaDetallesPedido($pedido['id']);
    $total     = number_format($pedido['total_iva'], 2);
    $tipo      = $tipo_map[$pedido['tipo']] ?? $pedido['tipo'];
    $hora      = date('H:i', strtotime($pedido['fecha_hora']));
    $productos = '';
    foreach ($detalles as $d) {
        $productos .= "<li>{$d['nombre']} x{$d['cantidad']}</li>";
    }
    return "
    <div style='border:1px solid #ddd; padding:20px; background:#fff; display:flex; flex-direction:column; gap:10px;'>
        <div style='display:flex; justify-content:space-between; align-items:center;'>
            <strong style='font-size:20px;'>Pedido #{$pedido['numero_dia']}</strong>
            <span style='background:#e2e8f0; padding:4px 10px; border-radius:3px; font-size:12px;'>{$tipo}</span>
        </div>
        <div style='color:#666; font-size:13px;'>
            Cliente: {$pedido['nombre_usuario']} &nbsp;|&nbsp; {$hora}
        </div>
        <ul style='margin:0; padding-left:18px; font-size:13px;'>{$productos}</ul>
        <div style='display:flex; justify-content:space-between; align-items:center; margin-top:5px;'>
            <strong>{$total} €</strong>
            <form method='POST' action='camarero_accion.php'>
                <input type='hidden' name='id_pedido'    value='{$pedido['id']}'>
                <input type='hidden' name='nuevo_estado' value='{$nuevo_estado}'>
                <button type='submit'
                    style='padding:8px 18px; background:black; color:white; border:none; cursor:pointer; font-size:13px;'>
                    {$texto_boton}
                </button>
            </form>
        </div>
    </div>";
}

// ── Construir contenido ───────────────────────────────────────────────────────
$contenidoPrincipal = "
<div style='padding:20px; max-width:1100px; margin:0 auto;'>

    <!-- Cabecera con avatar -->
    <div style='display:flex; align-items:center; gap:15px; margin-bottom:30px;'>
        <img src='{$rutaAvatar}' alt='Mi avatar'
             style='width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #ccc;'>
        <div>
            <div style='font-size:18px; font-weight:bold;'>Hola, {$user['nombre']} 👋</div>
            <div style='color:#666; font-size:13px;'>Vista Camarero</div>
        </div>
        <a href='" . RUTA_APP . "/index.php' style='margin-left:auto; text-decoration:none;'>
            <button style='padding:8px 15px; background:white; border:1px solid #bbb; cursor:pointer; font-size:13px;'>
                ← Inicio
            </button>
        </a>
    </div>

    <!-- Sección: Por Cobrar -->
    <h2 style='margin-top:0;'>Por Cobrar
        <span style='font-size:15px; color:#666; font-weight:normal;'>(pedidos recibidos, pendientes de pago)</span>
    </h2>";

if (empty($porCobrar)) {
    $contenidoPrincipal .= "<p style='color:#888;'>No hay pedidos pendientes de cobro.</p>";
} else {
    $contenidoPrincipal .= "<div style='display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:15px; margin-bottom:40px;'>";
    foreach ($porCobrar as $pedido) {
        $contenidoPrincipal .= tarjetaPedido($pedido, 'en preparación', 'Cobrado ✓', $tipo_map);
    }
    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= "
    <!-- Sección: Por Entregar -->
    <h2>Por Entregar
        <span style='font-size:15px; color:#666; font-weight:normal;'>(pedidos listos para el cliente)</span>
    </h2>";

if (empty($porEntregar)) {
    $contenidoPrincipal .= "<p style='color:#888;'>No hay pedidos listos para entregar.</p>";
} else {
    $contenidoPrincipal .= "<div style='display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:15px;'>";
    foreach ($porEntregar as $pedido) {
        $contenidoPrincipal .= tarjetaPedido($pedido, 'entregado', 'Entregado ✓', $tipo_map);
    }
    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
