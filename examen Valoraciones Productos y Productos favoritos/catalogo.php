<?php
require_once __DIR__.'/includes/config.php';

// Importamos la clase Producto
use es\ucm\fdi\aw\productos\Producto;

// Seguridad: Solo los clientes logueados pueden hacer pedidos
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

// Tipo de pedido que viene por la URL (GET) 
if (isset($_GET['tipo'])) {
    $tipo_elegido = $_GET['tipo'];
    if ($tipo_elegido == 'local' || $tipo_elegido == 'llevar') {
        $_SESSION['carrito'] = ['tipo' => $tipo_elegido, 'productos' => []];
        header('Location: catalogo.php');
        exit();
    }
}

// Si no hay carrito iniciado, lo echamos al inicio
if (!isset($_SESSION['carrito'])) {
    header('Location: index.php');
    exit();
}



$tituloPagina = 'Catálogo de Productos - Bistro FDI';

// CABECERA Y BOTÓN DEL CARRITO
$tipoTexto = ($_SESSION['carrito']['tipo'] == 'local') ? 'Consumir en Local' : 'Para Llevar';
$cantidadCarrito = 0;
foreach ($_SESSION['carrito']['productos'] as $item) {
    $cantidadCarrito += $item['cantidad'];
}

$categorias = Producto::listaCategorias();
$catActiva  = isset($_GET['categoria']) ? (int)$_GET['categoria'] : (!empty($categorias) ? $categorias[0]['id'] : 0);

// Cabecera del catálogo
// El span de la linea 53 es para el AYAX
$contenidoPrincipal = "
<div class='catalogo-header'>
    <div>
        <h1 class='mb-0'>Catálogo de Productos</h1>
        <p class='catalogo-tipo'>Tipo: {$tipoTexto}</p>
    </div>
    <div class='flex-fila gap-10'>
        <a href='recompensas.php'>
            <button type='button' class='btn-naranja btn-lg'>🎁 Recompensas</button>
        </a>
        <a href='carrito.php'>
            <button type='button' class='btn-oscuro btn-lg'>🛒 Ver Carrito (<span id='contador-carrito'>{$cantidadCarrito}</span>)</button>
        </a>
    </div>
</div>";

// Tabs de categorías
$contenidoPrincipal .= "<div class='catalogo-tabs'>";
foreach ($categorias as $cat) {
    $claseTab = ($cat['id'] == $catActiva) ? 'categoria-tab categoria-tab-activa' : 'categoria-tab';
    $contenidoPrincipal .= "<a href='catalogo.php?categoria={$cat['id']}' class='{$claseTab}'>" . htmlspecialchars($cat['nombre']) . "</a>";
}
$contenidoPrincipal .= "</div>";

// Grid de productos
$todosLosProductos = Producto::listaProductos(true);
$contenidoPrincipal .= "<div class='productos-grid'>";

$hayProductos = false;
foreach ($todosLosProductos as $prod) {
    if ($prod->getIdCategoria() == $catActiva && $prod->getDisponible()) {
        $hayProductos   = true;
        $precioFinal    = round($prod->getPrecioTotal(), 2);
        $precioFormateado = number_format($precioFinal, 2, ',', '.');
        $imgPrincipal   = $prod->getImagenPrincipal();
        $rutaImg        = $imgPrincipal
            ? RUTA_APP . "/img/productos/{$imgPrincipal}"
            : RUTA_APP . "/img/productos/default_food.png";

        $contenidoPrincipal .= "
        <div class='producto-card'>
            <div>
                <img src='{$rutaImg}' class='producto-imagen' alt='" . htmlspecialchars($prod->getNombre()) . "'>
                <h3 class='producto-nombre'>" . htmlspecialchars($prod->getNombre()) . "</h3>
                <p class='producto-descripcion'>" . htmlspecialchars($prod->getDescripcion()) . "</p>
                <h2 class='producto-precio'>{$precioFormateado} €</h2>
                <p class='producto-iva'>IVA {$prod->getIva()}% incluido</p>
            </div>
            <form method='POST' action='catalogo.php?categoria={$catActiva}' class='producto-form js-form-cantidad'>
                <input type='hidden' name='id_producto'     value='{$prod->getId()}'>
                <input type='hidden' name='nombre_producto' value='" . htmlspecialchars($prod->getNombre()) . "'>
                <input type='hidden' name='precio_final'    value='{$precioFinal}'>
                <input type='number' name='cantidad' value='1' min='1' max='99' step='1' class='producto-cantidad'>
                <button type='submit' class='btn-anadir'>+ Añadir</button>
            </form>
        </div>";
    }
}

if (!$hayProductos) {
    $contenidoPrincipal .= "<p>No hay productos disponibles en esta categoría.</p>";
}

$contenidoPrincipal .= "</div>";

$contenidoPrincipal .= <<<EOS
<!-- 1. Cargamos jQuery ANTES que tu código para que funcione el $ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="JS/validaciones.js"></script>
<script src="JS/catalogo.js"></script>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>