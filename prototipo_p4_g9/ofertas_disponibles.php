<?php
require_once __DIR__.'/includes/config.php';

use es\ucm\fdi\aw\ofertas\Oferta;

// Seguridad: solo clientes logueados pueden ver esta página 
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

$tituloPagina = 'Ofertones Disponibles - Bistro FDI';
$ofertas = Oferta::listaOfertasActivas();

$contenidoPrincipal = "
<a href='index.php' class='nav-link btn-flotante-inicio'>← Inicio</a>
<h1 class='mt-0 mb-10'>🔥 Ofertas Exclusivas</h1>
<p class='texto-gris mb-30'>Descubre nuestros mejores packs y menús con descuento. ¡Añade estos productos a tu carrito y disfruta del ahorro!</p>";

if (empty($ofertas)) {
    $contenidoPrincipal .= "
    <div class='ofertas-vacio'>
        <p class='texto-lg mb-10'>😔 No hay ofertas disponibles en este momento.</p>
        <p>Nuestros chefs están preparando nuevas promociones. ¡Vuelve pronto!</p>
    </div>";
} else {
    foreach ($ofertas as $oferta) {
        $nombre      = htmlspecialchars($oferta->getNombre());
        $descripcion = htmlspecialchars($oferta->getDescripcion());
        $descuento   = floatval($oferta->getPorcentajeDescuento());
        $validoHasta = (new \DateTime($oferta->getFechaFin()))->format('d/m/Y');

        $productosHtml = "";
        foreach ($oferta->getProductos() as $prod) {
            $nombreProd   = htmlspecialchars($prod['nombre']);
            $cantidadProd = htmlspecialchars($prod['cantidad_requerida']);
            $productosHtml .= "
            <div class='oferta-req-linea'>
                <span>✔️ {$nombreProd}</span>
                <span class='oferta-qty'>x{$cantidadProd}</span>
            </div>";
        }

        $contenidoPrincipal .= "
        <div class='tarjeta-oferta'>
            <div class='oferta-ribbon'>-{$descuento}%</div>
            <div class='oferta-cuerpo'>
                <h2 class='oferta-nombre'>{$nombre}</h2>
                <p class='oferta-descripcion'>{$descripcion}</p>
            </div>
            <div class='oferta-reqs'>
                <div class='oferta-reqs-titulo'>¿Qué debes pedir para activarla?</div>
                {$productosHtml}
            </div>
            <div class='oferta-footer'>
                <span class='oferta-fecha'>⏳ Válido hasta: {$validoHasta}</span>
            </div>
        </div>";
    }
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
