<?php
require_once __DIR__.'/includes/config.php';

// Importamos la clase Pedido
use es\ucm\fdi\aw\pedidos\Pedido;

// Seguridad básica
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente' || !isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    header('Location: catalogo.php');
    exit();
}

// 1. Recopilar datos del carrito
$totalPedido = 0;
foreach ($_SESSION['carrito']['productos'] as $item) {
    $totalPedido += $item['precio'] * $item['cantidad'];
}

// Sacamos el tipo de pedido que tienes guardado en tu sesión
$tipoPedido = $_SESSION['carrito']['tipo'];

// Guardamos copia de los productos para pintar el ticket
$ticketProductos = $_SESSION['carrito']['productos'];

// El estado inicial depende del método de pago
$metodoPago = $_POST['metodo_pago'] ?? 'camarero';
$estadoInicial = ($metodoPago === 'tarjeta') ? 'en preparación' : 'recibido';

// 2. Guardar en BD - ¡CAMBIADO A LLAMADA ESTÁTICA!
$resultadoBD = Pedido::creaPedido($_SESSION['id'], $tipoPedido, $totalPedido, $_SESSION['carrito'], $estadoInicial);

// 3. Comprobar si ha ido bien
$tituloPagina = 'Procesando... - Bistro FDI';

if ($resultadoBD['exito']) {
    
    // Vaciamos el carrito
    unset($_SESSION['carrito']);
    
    // Preparamos variables para la vista
    $numPedido = $resultadoBD['numero_dia'];
    $fechaFormat = date('d/m/Y, H:i:s', strtotime($resultadoBD['fecha_hora']));
    $nombreCliente = $_SESSION['nombre']; 
    $textoTipo = ($tipoPedido == 'local') ? 'Consumir en Local' : 'Para Llevar';
    $totalFormateado = number_format($totalPedido, 2);
    
    // Pantalla de éxito
    $contenidoPrincipal = "
    <div style='max-width: 600px; margin: 40px auto; text-align: center;'>

        <div style='margin-bottom: 20px; font-size: 72px; line-height: 1;'>
            ☑︎
        </div>

        <h1 style='margin: 0; font-size: 24px;'>¡Pedido Confirmado!</h1>
        <p style='color: #666; font-size: 14px; margin-top: 8px; margin-bottom: 30px;'>Tu pedido ha sido procesado correctamente</p>

        <div style='border: 1px solid #ddd; padding: 30px; text-align: left; background-color: #fff;'>
            
            <div style='text-align: center; margin-bottom: 30px;'>
                <div style='color: #666; font-size: 13px; margin-bottom: 5px;'>Número de Pedido</div>
                <div style='font-size: 36px; font-weight: bold;'>#{$numPedido}</div>
            </div>

            <div style='font-size: 14px; margin-bottom: 30px;'>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span style='color: #666;'>Estado:</span>
                    <span>" . ($estadoInicial === 'en preparación' ? 'En Preparación' : 'Recibido — pendiente de pago al camarero') . "</span>
                </div>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span style='color: #666;'>Tipo:</span>
                    <span>{$textoTipo}</span>
                </div>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span style='color: #666;'>Fecha y hora:</span>
                    <span>{$fechaFormat}</span>
                </div>
                <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                    <span style='color: #666;'>Cliente:</span>
                    <span>{$nombreCliente}</span>
                </div>
            </div>

            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>

            <div style='margin-bottom: 20px;'>
                <div style='font-weight: bold; margin-bottom: 15px; font-size: 14px;'>Productos</div>";

                foreach ($ticketProductos as $item) {
                    $subtotal = number_format($item['precio'] * $item['cantidad'], 2);
                    $contenidoPrincipal .= "
                    <div style='display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 13px;'>
                        <span>{$item['nombre']} x{$item['cantidad']}</span>
                        <span>{$subtotal} €</span>
                    </div>";
                }

    $contenidoPrincipal .= "
            </div>

            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>

            <div style='display: flex; justify-content: space-between; font-size: 16px;'>
                <strong>Total:</strong>
                <strong>{$totalFormateado} €</strong>
            </div>
        </div>

        <div style='display: flex; gap: 15px; margin-top: 30px;'>
            <a href='historial_pedidos.php' style='flex: 1; text-decoration: none;'>
                <button style='width: 100%; padding: 12px; font-size: 14px; background: white; color: black; border: 1px solid #ccc; cursor: pointer;'>
                    Ver Historial
                </button>
            </a>
            <a href='catalogo.php' style='flex: 1; text-decoration: none;'>
                <button style='width: 100%; padding: 12px; font-size: 14px; background: black; color: white; border: 1px solid black; cursor: pointer;'>
                    Volver al Inicio
                </button>
            </a>
        </div>
    </div>
    ";

} else {
    // Si la transacción falló en base de datos
    $tituloPagina = 'Error en el pedido - Bistro FDI';
    $contenidoPrincipal = "
    <div style='max-width: 600px; margin: 40px auto; text-align: center;'>
        <h1 style='color: red;'>¡Ups! Ha ocurrido un error</h1>
        <p>No hemos podido guardar tu pedido. Por favor, inténtalo de nuevo.</p>
        <a href='pago.php' style='text-decoration: none;'>
            <button style='padding: 10px 20px; background: black; color: white; border: none; cursor: pointer;'>Volver a intentar</button>
        </a>
    </div>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php'; 
?>