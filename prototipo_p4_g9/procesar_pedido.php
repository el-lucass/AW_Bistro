<?php
require_once __DIR__.'/includes/config.php';

// Importamos la clase Pedido
use es\ucm\fdi\aw\pedidos\Pedido;
use es\ucm\fdi\aw\usuarios\Usuario;

// Seguridad básica
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente' || !isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    header('Location: catalogo.php');
    exit();
}

// Recopilar datos del carrito
$subtotalPedido = 0;
foreach ($_SESSION['carrito']['productos'] as $item) {
    $subtotalPedido += $item['precio'] * $item['cantidad'];
}

// Recuperamos el total con descuento que calculamos en carrito.php
$totalFinal = $_SESSION['carrito']['total_final'] ?? $subtotalPedido;
$descuentoTotal = $subtotalPedido - $totalFinal;

// Sacamos el tipo de pedido que tienes guardado en tu sesión
$tipoPedido = $_SESSION['carrito']['tipo'];

// Guardamos copia de los productos para pintar el ticket
$ticketProductos = $_SESSION['carrito']['productos'];

$bistrocoinsGastadas = 0;

foreach ($_SESSION['carrito']['productos'] as $item) {
    if (!empty($item['es_recompensa'])) {
        $bistrocoinsGastadas += $item['bistrocoins'] * $item['cantidad'];
    }
}

// El estado inicial depende del método de pago
$pedidoGratis = isset($_GET['gratis']) && $_GET['gratis'] == '1';

$metodoPago = $_POST['metodo_pago'] ?? ($pedidoGratis ? 'bistrocoins' : 'camarero');

$estadoInicial = ($metodoPago === 'tarjeta' || $metodoPago === 'bistrocoins')
    ? 'en preparación'
    : 'recibido';

// Guardar en BD
$resultadoBD = Pedido::creaPedido(
    $_SESSION['id'],
    $tipoPedido,
    $subtotalPedido,
    $descuentoTotal,
    $totalFinal,
    $_SESSION['carrito'],
    $estadoInicial
);

// Comprobar si ha ido bien
$tituloPagina = 'Procesando... - Bistro FDI';

if ($resultadoBD['exito']) {
    
    if ($bistrocoinsGastadas > 0) {
        Usuario::restaBistrocoins($_SESSION['id'], $bistrocoinsGastadas);
    }

    if ($estadoInicial === 'en preparación') {
        $bistrocoinsGanadas = floor($totalFinal);

        if ($bistrocoinsGanadas > 0) {
            Usuario::sumaBistrocoins($_SESSION['id'], $bistrocoinsGanadas);
        }
    }

    // Vaciamos el carrito
    unset($_SESSION['carrito']);

    // Preparamos variables para la vista
    $numPedido   = $resultadoBD['numero_dia'];
    $fechaFormat = date('d/m/Y, H:i:s', strtotime($resultadoBD['fecha_hora']));
    $nombreCliente = $_SESSION['nombre'];
    $textoTipo   = ($tipoPedido == 'local') ? 'Consumir en Local' : 'Para Llevar';
    if ($metodoPago === 'bistrocoins') {
        $textoEstado = 'En Preparación — pagado con BistroCoins';
    } else {
        $textoEstado = ($estadoInicial === 'en preparación')
            ? 'En Preparación'
            : 'Recibido — pendiente de pago al camarero';
    }

    $subtotalFormateado  = number_format($subtotalPedido, 2);
    $descuentoFormateado = number_format($descuentoTotal, 2);
    $totalFinalFormateado = number_format($totalFinal, 2);

    // Pantalla de éxito
    $contenidoPrincipal = "
    <div class='pagina-estrecha'>
        <div class='ticket-icono'>☑︎</div>
        <h1 class='mb-0'>¡Pedido Confirmado!</h1>
        <p class='texto-gris texto-14 mt-8 mb-30'>Tu pedido ha sido procesado correctamente</p>

        <div class='ticket-card'>
            <div class='ticket-num-centro'>
                <div class='ticket-num-label'>Número de Pedido</div>
                <div class='ticket-num-valor'>#{$numPedido}</div>
            </div>

            <div class='ticket-info'>
                <div class='ticket-linea'>
                    <span class='ticket-linea-label'>Estado:</span>
                    <span>{$textoEstado}</span>
                </div>
                <div class='ticket-linea'>
                    <span class='ticket-linea-label'>Tipo:</span>
                    <span>{$textoTipo}</span>
                </div>
                <div class='ticket-linea'>
                    <span class='ticket-linea-label'>Fecha y hora:</span>
                    <span>{$fechaFormat}</span>
                </div>
                <div class='ticket-linea'>
                    <span class='ticket-linea-label'>Cliente:</span>
                    <span>{$nombreCliente}</span>
                </div>
            </div>

            <hr class='hr-sep'>

            <div class='mb-20'>
                <div class='ticket-productos-titulo'>Productos</div>";

    foreach ($ticketProductos as $item) {
        $esRecompensa = !empty($item['es_recompensa']);

        $nombreLinea = htmlspecialchars($item['nombre']);
        if ($esRecompensa) {
            $nombreLinea .= " (Recompensa)";
        }

        $subtotal = $esRecompensa
            ? "0.00"
            : number_format($item['precio'] * $item['cantidad'], 2);

        $contenidoPrincipal .= "
                <div class='ticket-producto'>
                    <span>{$nombreLinea} x{$item['cantidad']}</span>
                    <span>{$subtotal} €</span>
                </div>";
    }

    $contenidoPrincipal .= "</div><hr class='hr-sep'>";

    if ($descuentoTotal > 0) {
        $contenidoPrincipal .= "
            <div class='ticket-subtotal-linea'>
                <span>Subtotal:</span>
                <span>{$subtotalFormateado} €</span>
            </div>
            <div class='ticket-descuento'>
                <span>Descuento aplicado:</span>
                <span>- {$descuentoFormateado} €</span>
            </div>";
    }

    $contenidoPrincipal .= "
            <div class='ticket-total'>
                <strong>Total Pagado:</strong>
                <strong>{$totalFinalFormateado} €</strong>
            </div>
        </div>

        <div class='ticket-acciones'>
            <a href='historial_pedidos.php' class='flex-1'>
                <button class='btn-contorno btn-full btn-llg'>Ver Historial</button>
            </a>
            <a href='catalogo.php' class='flex-1'>
                <button class='btn-oscuro btn-full btn-llg'>Volver al Inicio</button>
            </a>
        </div>
    </div>";

} else {
    // Si la transacción falló en base de datos
    $tituloPagina = 'Error en el pedido - Bistro FDI';
    $contenidoPrincipal = "
    <div class='pagina-estrecha'>
        <h1 class='texto-rojo'>¡Ups! Ha ocurrido un error</h1>
        <p>No hemos podido guardar tu pedido. Por favor, inténtalo de nuevo.</p>
        <a href='pago.php'>
            <button class='btn-oscuro btn-lg mt-20'>Volver a intentar</button>
        </a>
    </div>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
