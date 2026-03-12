<?php
require_once __DIR__ . '/../includes/config.php';


use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Pedido;


if (!isset($_SESSION['login']) || !Usuario::tieneRol('camarero')) {
    header('Location: ' . RUTA_APP . '/login.php');
    exit();
}

$tituloPagina = 'Vista Camarero - Bistro FDI';



if ($user) {
    $avatarActual = $user->getAvatar();
    $nombreUsuario = htmlspecialchars($user->getNombre());
} else {
    $avatarActual = 'default.png';
    $nombreUsuario = 'Camarero';
}


if (strpos($avatarActual, 'predefinidos/') !== false) {
    $rutaAvatar = RUTA_IMGS . "avatares/" . $avatarActual;
} elseif ($avatarActual == 'default.png') {
    $rutaAvatar = RUTA_IMGS . "avatares/default.png";
} else {
    $rutaAvatar = RUTA_IMGS . "avatares/usuarios/" . $avatarActual;
}


$porCobrar    = Pedido::listaPedidosPorEstados(['recibido']);
$porTerminar  = Pedido::listaPedidosPorEstados(['listo cocina']);
$porEntregar  = Pedido::listaPedidosPorEstados(['terminado']);

$tipo_map = ['local' => 'Local', 'llevar' => 'Para Llevar'];


function tarjetaPedido($pedido, $nuevo_estado, $texto_boton, $tipo_map) {
    $id_pedido = $pedido->getId();
    
   
    $detalles  = Pedido::buscaDetallesPedido($id_pedido);
    
    $total     = number_format($pedido->getTotalIva(), 2);
    $tipoRaw   = $pedido->getTipo();
    $tipo      = $tipo_map[$tipoRaw] ?? $tipoRaw;
    $hora      = date('H:i', strtotime($pedido->getFechaHora()));
    
    $nombreCliente = htmlspecialchars($pedido->getNombreUsuario());
    $numDia = htmlspecialchars($pedido->getNumeroDia());
    
    $productos = '';
    foreach ($detalles as $d) {
        $productos .= "<li>" . htmlspecialchars($d['nombre']) . " x{$d['cantidad']}</li>";
    }
    
    return "
    <div style='border:1px solid #ddd; padding:20px; background:#fff; display:flex; flex-direction:column; gap:10px;'>
        <div style='display:flex; justify-content:space-between; align-items:center;'>
            <strong style='font-size:20px;'>Pedido #{$numDia}</strong>
            <span style='background:#e2e8f0; padding:4px 10px; border-radius:3px; font-size:12px;'>{$tipo}</span>
        </div>
        <div style='color:#666; font-size:13px;'>
            Cliente: {$nombreCliente} &nbsp;|&nbsp; {$hora}
        </div>
        <ul style='margin:0; padding-left:18px; font-size:13px;'>{$productos}</ul>
        <div style='display:flex; justify-content:space-between; align-items:center; margin-top:5px;'>
            <strong>{$total} €</strong>
            <form method='POST' action='camarero_accion.php'>
                <input type='hidden' name='id_pedido'    value='{$id_pedido}'>
                <input type='hidden' name='nuevo_estado' value='{$nuevo_estado}'>
                <button type='submit'
                    style='padding:8px 18px; background:black; color:white; border:none; cursor:pointer; font-size:13px;'>
                    {$texto_boton}
                </button>
            </form>
        </div>
    </div>";
}


<div style='padding:20px; max-width:1100px; margin:0 auto;'>

    <div style='display:flex; align-items:center; gap:15px; margin-bottom:30px;'>
        <img src='{$rutaAvatar}' alt='Mi avatar'
             style='width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #ccc;'>
        <div>
            <div style='font-size:18px; font-weight:bold;'>Hola, {$nombreUsuario} 👋</div>
            <div style='color:#666; font-size:13px;'>Vista Camarero</div>
        </div>
        <a href='" . RUTA_APP . "/index.php' style='margin-left:auto; text-decoration:none;'>
            <button style='padding:8px 15px; background:white; border:1px solid #bbb; cursor:pointer; font-size:13px;'>
                ← Inicio
            </button>
        </a>
    </div>

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
    <h2>Por Revisar
        <span style='font-size:15px; color:#666; font-weight:normal;'>(listos en cocina, pendientes de revisión del camarero)</span>
    </h2>";

if (empty($porTerminar)) {
    $contenidoPrincipal .= "<p style='color:#888;'>No hay pedidos listos en cocina.</p>";
} else {
    $contenidoPrincipal .= "<div style='display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:15px; margin-bottom:40px;'>";
    foreach ($porTerminar as $pedido) {
        $contenidoPrincipal .= tarjetaPedido($pedido, 'terminado', 'Revisado ✓', $tipo_map);
    }
    $contenidoPrincipal .= "</div>";
}

$contenidoPrincipal .= "
    <h2>Por Entregar
        <span style='font-size:15px; color:#666; font-weight:normal;'>(pedidos revisados, listos para el cliente)</span>
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