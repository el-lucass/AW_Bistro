<?php
require_once 'includes/config.php';
require_once 'includes/productos.php'; //Archivo de productos usando tu estructura de carpetas

// Seguridad: Solo los clientes logueados pueden hacer pedidos
if (!isset($_SESSION['login']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

// Tipo de pedido que viene por la URL (GET) 
if (isset($_GET['tipo'])) {
    $tipo_elegido = $_GET['tipo'];
    if ($tipo_elegido == 'local' || $tipo_elegido == 'llevar') {
        $_SESSION['carrito'] = [
            'tipo' => $tipo_elegido,
            'productos' => [] 
        ];
        // Redirigimos al mismo archivo para limpiar la URL
        header('Location: catalogo.php'); 
        exit();
    }
}

// Si no hay carrito iniciado, lo echamos al inicio
if (!isset($_SESSION['carrito'])) {
    header('Location: index.php');
    exit();
}

// LÓGICA DE AÑADIR PRODUCTOS AL CARRITO 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_producto'])) {
    $id_producto = $_POST['id_producto'];
    $nombre_producto = $_POST['nombre_producto'];
    $precio_final = $_POST['precio_final'];
    $cantidad = (int)$_POST['cantidad'];
    
    $encontrado = false;
    foreach ($_SESSION['carrito']['productos'] as &$item) {
        if ($item['id_producto'] == $id_producto) {
            $item['cantidad'] += $cantidad;
            $encontrado = true;
            break;
        }
    }
    
    if (!$encontrado) {
        $_SESSION['carrito']['productos'][] = [
            'id_producto' => $id_producto,
            'nombre' => $nombre_producto,
            'precio' => $precio_final,
            'cantidad' => $cantidad
        ];
    }
    
    $catRedireccion = isset($_GET['categoria']) ? (int)$_GET['categoria'] : '';
    header("Location: catalogo.php?categoria={$catRedireccion}");
    exit();
}


$tituloPagina = 'Catálogo de Productos - Bistro FDI';
$contenidoPrincipal = "<div style='padding: 20px;'>";

// CABECERA Y BOTÓN DEL CARRITO
$tipoTexto = ($_SESSION['carrito']['tipo'] == 'local') ? 'Consumir en Local' : 'Para Llevar';

$cantidadCarrito = 0;
foreach ($_SESSION['carrito']['productos'] as $item) {
    $cantidadCarrito += $item['cantidad'];
}

$contenidoPrincipal .= "
<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;'>
    <div>
        <h1 style='margin: 0;'>Catálogo de Productos</h1>
        <p style='color: #666; margin-top: 5px;'>Tipo: {$tipoTexto}</p>
    </div>
    <div>
        <a href='carrito.php' style='text-decoration: none;'>
            <button style='padding: 10px 20px; font-size: 16px; border: 1px solid black; background: white; cursor: pointer;'>
                🛒 Ver Carrito ({$cantidadCarrito})
            </button>
        </a>
    </div>
</div>";

// OBTENER CATEGORÍAS 
$categorias = listaCategorias(); 

$catActiva = isset($_GET['categoria']) ? (int)$_GET['categoria'] : (!empty($categorias) ? $categorias[0]['id'] : 0);

$contenidoPrincipal .= "<div style='display: flex; gap: 30px; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 30px; overflow-x: auto;'>";
foreach ($categorias as $cat) {
    $estiloEnlace = "text-decoration: none; color: black; font-size: 16px;";
    if ($cat['id'] == $catActiva) {
        $estiloEnlace .= " font-weight: bold; border-bottom: 2px solid black; padding-bottom: 11px;"; 
    }
    $contenidoPrincipal .= "<a href='catalogo.php?categoria={$cat['id']}' style='{$estiloEnlace}'>{$cat['nombre']}</a>";
}
$contenidoPrincipal .= "</div>";

// OBTENER PRODUCTOS 
$todosLosProductos = listaProductos(true); 

$productosCategoria = [];
foreach ($todosLosProductos as $prod) {
    if ($prod['id_categoria'] == $catActiva && $prod['disponible'] == 1) {
        $productosCategoria[] = $prod;
    }
}

$contenidoPrincipal .= "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";

if (!empty($productosCategoria)) {
    foreach ($productosCategoria as $prod) {
        
        $precioFinal = $prod['precio_base'] * (1 + $prod['iva'] / 100);
        $precioFormateado = number_format($precioFinal, 2);
        
        $htmlImagen = "";
        if (!empty($prod['imagen_principal'])) {
            $htmlImagen = "<img src='" . RUTA_APP . "/img/productos/{$prod['imagen_principal']}' style='width: 100%; height: 150px; object-fit: contain; margin-bottom: 15px;' alt='{$prod['nombre']}'>";
        }

        $contenidoPrincipal .= "
        <div style='border: 1px solid #e0e0e0; padding: 20px; width: 300px; display: flex; flex-direction: column; justify-content: space-between;'>
            <div>
                {$htmlImagen}
                <h3 style='margin: 0 0 10px 0; font-size: 18px;'>{$prod['nombre']}</h3>
                <p style='color: #666; font-size: 14px; margin: 0 0 15px 0; min-height: 40px;'>{$prod['descripcion']}</p>
                
                <h2 style='margin: 0; font-size: 24px;'>{$precioFormateado} €</h2>
                <p style='color: #999; font-size: 12px; margin: 5px 0 15px 0;'>IVA {$prod['iva']}% incluido</p>
            </div>
            
            <form method='POST' action='catalogo.php?categoria={$catActiva}' style='display: flex; gap: 10px; margin: 0;'>
                <input type='hidden' name='id_producto' value='{$prod['id']}'>
                <input type='hidden' name='nombre_producto' value='{$prod['nombre']}'>
                <input type='hidden' name='precio_final' value='{$precioFinal}'>
                
                <input type='number' name='cantidad' value='1' min='1' style='width: 60px; padding: 5px; border: 1px solid #ccc;'>
                <button type='submit' style='flex-grow: 1; background: black; color: white; border: none; padding: 10px; cursor: pointer; font-weight: bold;'>
                    + Añadir
                </button>
            </form>
        </div>";
    }
} else {
    $contenidoPrincipal .= "<p>No hay productos disponibles en esta categoría en este momento.</p>";
}

$contenidoPrincipal .= "</div></div>"; 

require RAIZ_APP . '/vistas/plantillas/plantilla.php'; 
?>