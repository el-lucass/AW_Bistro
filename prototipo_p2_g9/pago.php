<?php
require_once 'includes/config.php';

// Seguridad: Solo clientes logueados
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

// Si el carrito está vacío, no pintamos nada aquí, de vuelta al catálogo
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    header('Location: catalogo.php');
    exit();
}

$tituloPagina = 'Pago - Bistro FDI';
$contenidoPrincipal = "<div style='padding: 20px; max-width: 800px; margin: 0 auto;'>";

//BOTÓN DE VOLVER
$contenidoPrincipal .= "
<div style='margin-bottom: 20px; display: flex; justify-content: flex-start;'>
    <a href='carrito.php' style='text-decoration: none;'>
        <button type='button' style='background-color: white; color: #333; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; transition: 0.2s;'>
            ← Volver al carrito
        </button>
    </a>
</div>
<h1 style='margin-top: 0; margin-bottom: 30px;'>Pago</h1>
";

// Empezamos el formulario que enviará los datos a procesar el pedido
$contenidoPrincipal .= "<form action='procesar_pedido.php' method='POST' id='form_pago'>";

// RESUMEN DEL PEDIDO
$contenidoPrincipal .= "
<div style='border: 1px solid #eee; padding: 25px; margin-bottom: 25px; border-radius: 5px;'>
    <h3 style='margin-top: 0; margin-bottom: 20px; font-size: 16px;'>Resumen del Pedido</h3>
";

$totalCarrito = 0;
foreach ($_SESSION['carrito']['productos'] as $item) {
    $subtotal = $item['precio'] * $item['cantidad'];
    $totalCarrito += $subtotal;
    $precioFormateado = number_format($subtotal, 2);
    
    $contenidoPrincipal .= "
    <div style='display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px;'>
        <span>{$item['nombre']} x{$item['cantidad']}</span>
        <span>{$precioFormateado} €</span>
    </div>
    ";
}

$totalFormateado = number_format($totalCarrito, 2);
$contenidoPrincipal .= "
    <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
    <div style='display: flex; justify-content: space-between; font-size: 18px;'>
        <strong>Total:</strong>
        <strong>{$totalFormateado} €</strong>
    </div>
</div>
";

//SELECCIÓN DE MÉTODO DE PAGO
$contenidoPrincipal .= "
<div style='border: 1px solid #eee; padding: 25px; margin-bottom: 25px; border-radius: 5px;'>
    <h3 style='margin-top: 0; margin-bottom: 20px; font-size: 16px;'>Método de Pago</h3>
    
    <div style='display: flex; gap: 20px;'>
        <div id='btn_tarjeta' onclick=\"seleccionarMetodo('tarjeta')\" style='flex: 1; border: 1px solid #ccc; padding: 20px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.2s;'>
            💳 <span>Tarjeta de Crédito/Débito</span>
        </div>
        
        <div id='btn_camarero' onclick=\"seleccionarMetodo('camarero')\" style='flex: 1; border: 2px solid black; padding: 20px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.2s;'>
            👤 <span>Pagar al Camarero</span>
        </div>
    </div>
    
    <input type='hidden' name='metodo_pago' id='input_metodo' value='camarero'>
</div>
";

//FORMULARIO DE TARJETA (no visible por defecto) 
$contenidoPrincipal .= "
<div id='formulario_tarjeta' style='display: none; border: 1px solid #eee; padding: 25px; margin-bottom: 30px; border-radius: 5px; background-color: #fafafa;'>
    <h3 style='margin-top: 0; margin-bottom: 15px; font-size: 16px;'>Datos de la Tarjeta</h3>
    
    <div style='margin-bottom: 15px;'>
        <label style='display: block; margin-bottom: 5px; font-size: 14px;'>Titular de la tarjeta</label>
        <input type='text' name='titular_tarjeta' placeholder='Ej. Juan Pérez' style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>
    </div>
    
    <div style='margin-bottom: 15px;'>
        <label style='display: block; margin-bottom: 5px; font-size: 14px;'>Número de tarjeta</label>
        <input type='text' name='num_tarjeta' placeholder='1234567891011121' maxlength='16' style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>    </div>
    
    <div style='display: flex; gap: 15px;'>
        <div style='flex: 1;'>
            <label style='display: block; margin-bottom: 5px; font-size: 14px;'>Caducidad (MM/AA)</label>
            <input type='text' name='caducidad_tarjeta' placeholder='MM/AA' style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>
        </div>
        <div style='flex: 1;'>
            <label style='display: block; margin-bottom: 5px; font-size: 14px;'>CVV</label>
            <input type='text' name='cvv_tarjeta' placeholder='123' maxlength='4' style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>        </div>
    </div>
</div>
";

//INFO PAGO AL CAMARERO (Visible por defecto)
$contenidoPrincipal .= "
<div id='info_camarero' style='border: 1px solid #eee; padding: 25px; margin-bottom: 30px; border-radius: 5px; background-color: #fafafa;'>
    <h3 style='margin-top: 0; margin-bottom: 10px; font-size: 16px;'>Pago al Camarero</h3>
    <p style='color: #666; font-size: 14px; margin: 0;'>Tu pedido se procesará y podrás realizar el pago directamente al camarero cuando recojas o recibas tu pedido.</p>
</div>
";

//BOTÓN DE CONFIRMAR
$contenidoPrincipal .= "
<div style='display: flex; justify-content: flex-end;'>
    <button type='submit' style='padding: 10px 30px; font-size: 14px; background: black; color: white; border: none; cursor: pointer; border-radius: 5px;'>
        Confirmar Pago
    </button>
</div>

</form>
</div>
";

//Inputs del pago con tarjeta
$contenidoPrincipal .= "
<script>
function seleccionarMetodo(metodo) {
    document.getElementById('input_metodo').value = metodo;

    const btnTarjeta = document.getElementById('btn_tarjeta');
    const btnCamarero = document.getElementById('btn_camarero');
    const formTarjeta = document.getElementById('formulario_tarjeta');
    const infoCamarero = document.getElementById('info_camarero');
    
    const inputsTarjeta = formTarjeta.querySelectorAll('input');

    if (metodo === 'tarjeta') {
        btnTarjeta.style.border = '2px solid black';
        btnCamarero.style.border = '1px solid #ccc';
        
        formTarjeta.style.display = 'block';
        infoCamarero.style.display = 'none';
        
        // Sigue haciendo los campos obligatorios
        inputsTarjeta.forEach(input => input.required = true);
        
    } else {
        btnCamarero.style.border = '2px solid black';
        btnTarjeta.style.border = '1px solid #ccc';
        
        formTarjeta.style.display = 'none';
        infoCamarero.style.display = 'block';
        
        // Quita la obligación si paga al camarero
        inputsTarjeta.forEach(input => input.required = false);
    }
}
</script>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php'; 
?>