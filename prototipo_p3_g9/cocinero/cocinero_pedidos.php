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
$pedidos    = Cocina::listaPedidosEnPreparacion();
$tituloPagina = 'Cocina - Bistro FDI';

$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'no_disponible') {
        $msg = "<p class='msg-error'>Ese pedido ya no está disponible (otro cocinero lo cogió o cambió el estado).</p>";
    } elseif ($_GET['msg'] === 'listo') {
        $msg = "<p class='msg-exito'>✅ Pedido terminado.</p>";
    }
}

$contenidoPrincipal = "
<h1 class='mt-0'>Cocina</h1>
{$msg}

<h2 class='mt-25 mb-10'>Mis pedidos en curso</h2>
<div class='panel-tabla'>";

if (empty($misPedidos)) {
    $contenidoPrincipal .= "<div class='panel-vacio'>No tienes pedidos en curso.</div>";
} else {
    $contenidoPrincipal .= "
    <table>
        <thead><tr>
            <th>#</th><th>Fecha</th><th>Tipo</th><th>Cliente</th><th class='texto-centro'>Ir</th>
        </tr></thead>
        <tbody>";

    foreach ($misPedidos as $p) {
        $num    = htmlspecialchars($p['numero_dia']);
        $fecha  = date('d/m/Y H:i', strtotime($p['fecha_hora']));
        $tipo   = ($p['tipo'] === 'local') ? 'Local' : 'Para llevar';
        $cliente = htmlspecialchars($p['nombre_usuario']);

        $contenidoPrincipal .= "
        <tr>
            <td>#{$num}</td>
            <td>{$fecha}</td>
            <td>{$tipo}</td>
            <td>{$cliente}</td>
            <td class='texto-centro'>
                <a href='cocinero_pedido.php?id={$p['id']}'>
                    <button class='btn-oscuro btn-sm'>Ver</button>
                </a>
            </td>
        </tr>";
    }
    $contenidoPrincipal .= "</tbody></table>";
}
$contenidoPrincipal .= "</div>";

$contenidoPrincipal .= "
<h2 class='mt-10 mb-10'>Pedidos en preparación (Sin asignar)</h2>
<div class='panel-tabla'>";

if (empty($pedidos)) {
    $contenidoPrincipal .= "<div class='panel-vacio'>No hay pedidos en preparación ahora mismo.</div>";
} else {
    $contenidoPrincipal .= "
    <table>
        <thead><tr>
            <th>#</th><th>Fecha</th><th>Tipo</th><th>Cliente</th>
            <th class='texto-derecha'>Total</th><th class='texto-centro'>Acción</th>
        </tr></thead>
        <tbody>";

    foreach ($pedidos as $p) {
        $num    = htmlspecialchars($p['numero_dia']);
        $fecha  = date('d/m/Y H:i', strtotime($p['fecha_hora']));
        $tipo   = ($p['tipo'] === 'local') ? 'Local' : 'Para llevar';
        $cliente = htmlspecialchars($p['nombre_usuario']);
        $total  = number_format((float)$p['total_iva'], 2);

        $contenidoPrincipal .= "
        <tr>
            <td>#{$num}</td>
            <td>{$fecha}</td>
            <td>{$tipo}</td>
            <td>{$cliente}</td>
            <td class='texto-derecha'>{$total} €</td>
            <td class='texto-centro'>
                <form method='POST' action='cocinero_accion.php' class='inline'>
                    <input type='hidden' name='accion' value='coger_pedido'>
                    <input type='hidden' name='id_pedido' value='{$p['id']}'>
                    <button type='submit' class='btn-oscuro btn-sm'>Cocinar</button>
                </form>
            </td>
        </tr>";
    }
    $contenidoPrincipal .= "</tbody></table>";
}
$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
