<?php
require_once __DIR__.'/includes/config.php';

// Importamos las clases
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\Oferta;

// Seguridad: Solo los clientes logueados pueden ver el carrito
if (!isset($_SESSION['login']) || !Usuario::tieneRol('cliente')) {
    header('Location: login.php');
    exit();
}

// Lógica para vaciar/cancelar el carrito
if (isset($_GET['accion']) && $_GET['accion'] == 'vaciar') {
    unset($_SESSION['carrito']);
    header('Location: index.php');
    exit();
}

// Lógica para eliminar un solo producto
if (isset($_GET['eliminar'])) {
    $id_a_eliminar = $_GET['eliminar'];
    $esRecompensaUrl = isset($_GET['recompensa']) && $_GET['recompensa'] == '1';

    foreach ($_SESSION['carrito']['productos'] as $key => $item) {
        $itemEsRecompensa = !empty($item['es_recompensa']);

        if ($item['id_producto'] == $id_a_eliminar && $itemEsRecompensa == $esRecompensaUrl) {
            unset($_SESSION['carrito']['productos'][$key]);
            break;
        }
    }

    $_SESSION['carrito']['productos'] = array_values($_SESSION['carrito']['productos']);

    header('Location: carrito.php');
    exit();
}

// Lógica para actualizar cantidades 
if (isset($_GET['actualizar']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $op = $_GET['actualizar'];
    $esRecompensaUrl = isset($_GET['recompensa']) && $_GET['recompensa'] == '1';

    foreach ($_SESSION['carrito']['productos'] as &$item) {
        $itemEsRecompensa = !empty($item['es_recompensa']);

        if ($item['id_producto'] == $id && $itemEsRecompensa == $esRecompensaUrl) {

            if ($op == 'sumar') {

                if ($itemEsRecompensa) {
                    $saldoBistrocoins = Usuario::getBistrocoins($_SESSION['id']);

                    $bistrocoinsActuales = 0;

                    foreach ($_SESSION['carrito']['productos'] as $productoCarrito) {
                        if (!empty($productoCarrito['es_recompensa'])) {
                            $bistrocoinsActuales += $productoCarrito['bistrocoins'] * $productoCarrito['cantidad'];
                        }
                    }

                    $costeExtra = $item['bistrocoins'];

                    if ($bistrocoinsActuales + $costeExtra > $saldoBistrocoins) {
                        $_SESSION['mensaje_error_carrito'] = "No tienes suficientes BistroCoins para añadir otra unidad de esta recompensa.";
                        header('Location: carrito.php');
                        exit();
                    }
                }

                $item['cantidad']++;

            } elseif ($op == 'restar') {

                $item['cantidad']--;

                if ($item['cantidad'] <= 0) {
                    header("Location: carrito.php?eliminar={$id}&recompensa=" . ($itemEsRecompensa ? '1' : '0'));
                    exit();
                }
            }

            break;
        }
    }

    unset($item);

    header('Location: carrito.php');
    exit();
}

$tituloPagina = 'Carrito de Compra - Bistro FDI';

// BOTÓN DE VOLVER 
$botonVolver = "
<div class='mb-20'>
    <a href='catalogo.php' class='nav-link'>← Volver al catálogo</a>
</div>";

// Verificamos si hay un carrito activo
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    $contenidoPrincipal = $botonVolver . "
    <h1>Carrito de Compra</h1>
    <p>Tu carrito está vacío en este momento.</p>";
} else {
    $tipoTexto = ($_SESSION['carrito']['tipo'] == 'local') ? 'Consumir en Local' : 'Para Llevar';

    $mensajeError = '';

    if (isset($_SESSION['mensaje_error_carrito'])) {
        $mensajeError = "
        <div class='alerta-error mb-20'>
            " . htmlspecialchars($_SESSION['mensaje_error_carrito']) . "
        </div>";
        unset($_SESSION['mensaje_error_carrito']);
    }

    $saldoBistrocoins = Usuario::getBistrocoins($_SESSION['id']);
    $bistrocoinsCarrito = 0;

    foreach ($_SESSION['carrito']['productos'] as $item) {
        if (!empty($item['es_recompensa'])) {
            $bistrocoinsCarrito += $item['bistrocoins'] * $item['cantidad'];
        }
    }

    $contenidoPrincipal = $botonVolver . "
    <h1 class='mb-5'>Carrito de Compra</h1>
    <p class='carrito-tipo'>Tipo: {$tipoTexto}</p>
    {$mensajeError}

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class='texto-centro'>Precio</th>
                <th class='texto-centro'>Cantidad</th>
                <th class='texto-derecha'>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>";

    $subtotalCarrito = 0;


    foreach ($_SESSION['carrito']['productos'] as $item) {

    $esRecompensa = !empty($item['es_recompensa']);

    $paramRecompensa = $esRecompensa ? '1' : '0';

    $subtotalLinea = $esRecompensa
        ? 0
        : $item['precio'] * $item['cantidad'];

    $subtotalCarrito += $subtotalLinea;

    $precioFormateado = $esRecompensa
        ? "0.00"
        : number_format($item['precio'], 2);

    $subtotalFormateado = number_format($subtotalLinea, 2);

    $nombre = htmlspecialchars($item['nombre']);

    $paramRecompensa = $esRecompensa ? '1' : '0';

    if ($esRecompensa) {
        $nombre .= " (Recompensa)";
    }

    $contenidoPrincipal .= "
        <tr>
            <td><strong>{$nombre}</strong></td>
            <td class='texto-centro'>{$precioFormateado} €</td>
            <td class='texto-centro'>
                <div class='flex-centro gap-10'>
                    <a href='carrito.php?actualizar=restar&id={$item['id_producto']}&recompensa={$paramRecompensa}'>
                        <button class='btn-cantidad'>-</button>
                    </a>
                    <span class='cantidad-display'>{$item['cantidad']}</span>
                    <a href='carrito.php?actualizar=sumar&id={$item['id_producto']}&recompensa={$paramRecompensa}'>
                        <button class='btn-cantidad'>+</button>
                    </a>
                </div>
            </td>
            <td class='texto-derecha'>{$subtotalFormateado} €</td>
            <td class='texto-centro'>
                <a href='carrito.php?eliminar={$item['id_producto']}&recompensa={$paramRecompensa}'>
                    <button class='btn-contorno btn-sm'>🗑️</button>
                </a>
            </td>
        </tr>";
}

    $contenidoPrincipal .= "</tbody></table>";

    $resultadoOfertas   = Oferta::aplicarOfertasAlCarrito($_SESSION['carrito']['productos']);
    $totalDescuento     = $resultadoOfertas['total_descuento'];
    $detallesDescuento  = $resultadoOfertas['detalles'];
    $totalFinal         = max(0, $subtotalCarrito - $totalDescuento);
    $_SESSION['carrito']['total_final'] = $totalFinal;

    $contenidoPrincipal .= "
    <div class='carrito-resumen-box'>
        <div class='resumen-linea'>
            <span>Subtotal:</span>
            <span>" . number_format($subtotalCarrito, 2) . " €</span>
        </div>
        <div class='resumen-linea'>
            <span>BistroCoins disponibles:</span>
            <span>{$saldoBistrocoins}</span>
        </div>
        <div class='resumen-linea'>
            <span>Vas a gastar:</span>
            <span>{$bistrocoinsCarrito} BistroCoins</span>
        </div>";

    if ($totalDescuento > 0) {
        foreach ($detallesDescuento as $desc) {
            $vecesTexto = ($desc['veces'] > 1) ? " (x{$desc['veces']})" : "";
            $contenidoPrincipal .= "
        <div class='resumen-descuento'>
            <span>(Oferta) " . htmlspecialchars($desc['nombre']) . $vecesTexto . ":</span>
            <span>- " . number_format($desc['ahorro'], 2) . " €</span>
        </div>";
        }
    }

    $contenidoPrincipal .= "
        <hr class='hr-sep-ddd'>
        <div class='resumen-total-fila'>
            <h2 class='resumen-total-label'>Total a pagar:</h2>
            <h2 class='resumen-total-valor'>" . number_format($totalFinal, 2) . " €</h2>
        </div>
    </div>

    <div class='carrito-acciones'>
        <a href='carrito.php?accion=vaciar' class='btn-contorno btn-lg'>Cancelar Pedido</a>
        <a href='" . ($totalFinal <= 0 ? "procesar_pedido.php?gratis=1" : "pago.php") . "' class='btn-oscuro btn-lg'>
            " . ($totalFinal <= 0 ? "Confirmar Pedido" : "Confirmar y Pagar") . "
        </a>
    </div>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
