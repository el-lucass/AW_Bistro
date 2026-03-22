<?php
require_once __DIR__.'/includes/config.php';

use es\ucm\fdi\aw\ofertas\Oferta;

// Seguridad: solo clientes logueados pueden ver esta página 
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

$tituloPagina = 'Ofertones Disponibles - Bistro FDI';

$contenidoPrincipal = "<div style='padding: 20px; max-width: 800px; margin: 0 auto;'>";

$estiloBotonVolver = "text-decoration: none; color: #333; background-color: white; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; display: inline-block;";

$contenidoPrincipal .= "
<div style='margin-bottom: 20px;'>
    <a href='index.php' style='{$estiloBotonVolver}'>
        ← Volver al inicio
    </a>
</div>
<h1 style='margin-top: 0; margin-bottom: 10px; font-size: 28px;'>🔥 Ofertas Exclusivas</h1>
<p style='color: #666; margin-bottom: 30px; font-size: 16px;'>Descubre nuestros mejores packs y menús con descuento. ¡Añade estos productos a tu carrito y disfruta del ahorro!</p>
";

// OBTENER OFERTAS ACTIVAS USANDO LA CLASE 
$ofertas = Oferta::listaOfertasActivas();

if (empty($ofertas)) {
    $contenidoPrincipal .= "
    <div style='text-align: center; color: #666; margin-top: 50px; border: 1px dashed #ccc; padding: 40px; border-radius: 8px;'>
        <p style='font-size: 20px; margin-bottom: 10px;'>😔 No hay ofertas disponibles en este momento.</p>
        <p style='font-size: 15px;'>Nuestros chefs están preparando nuevas promociones. ¡Vuelve pronto!</p>
    </div>";
} else {
    foreach ($ofertas as $oferta) {
        $nombre = htmlspecialchars($oferta->getNombre());
        $descripcion = htmlspecialchars($oferta->getDescripcion());
        $descuento = floatval($oferta->getPorcentajeDescuento()); 
        
        // Fecha de validez
        $fechaFin = new \DateTime($oferta->getFechaFin());
        $validoHasta = $fechaFin->format('d/m/Y');

        // Productos que incluye la oferta
        $productosHtml = "";
        $productosArray = $oferta->getProductos();
        
        if (!empty($productosArray)) {
            foreach ($productosArray as $prod) {
                $nombreProd = htmlspecialchars($prod['nombre']);
                $cantidadProd = htmlspecialchars($prod['cantidad_requerida']);
                $productosHtml .= "
                <div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #333;'>
                    <span>✔️ {$nombreProd}</span>
                    <span style='font-weight: bold; background: #eee; padding: 2px 8px; border-radius: 10px;'>x{$cantidadProd}</span>
                </div>";
            }
        }

        // Tarjeta de la oferta
        $contenidoPrincipal .= "
        <div style='border: 1px solid #ddd; border-radius: 8px; padding: 25px; margin-bottom: 25px; background-color: #fff; position: relative; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'>
            
            <div style='position: absolute; top: 20px; right: -35px; background-color: #000000; color: white; padding: 5px 40px; transform: rotate(45deg); font-weight: bold; font-size: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);'>
                -{$descuento}%
            </div>

            <div style='padding-right: 60px;'>
                <h2 style='margin: 0 0 10px 0; font-size: 22px; color: #2c3e50;'>{$nombre}</h2>
                <p style='color: #7f8c8d; font-size: 15px; margin-bottom: 20px; line-height: 1.5;'>{$descripcion}</p>
            </div>
            
            <div style='background-color: #f8f9fa;  padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #eee;'>
                <div style='font-weight: bold; margin-bottom: 12px; font-size: 15px; color: #34495e; border-bottom: 1px solid #ddd; padding-bottom: 8px;'>¿Qué debes pedir para activarla?</div>
                {$productosHtml}
            </div>
            
            <div style='display: flex; justify-content: space-between; align-items: center;'>
                <div style='font-size: 13px; color: #95a5a6; font-style: italic;'>
                    ⏳ Válido hasta: {$validoHasta}
                </div>
            </div>
        </div>";
    }
}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>