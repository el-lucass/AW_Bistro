<?php
require_once __DIR__.'/includes/config.php';

// Chivatos para saber qué falla exactamente
if (!isset($_SESSION['login'])) { echo "Error: Usuario no logueado"; exit(); }
if ($_SESSION['rol'] != 'cliente') { echo "Error: El rol no es cliente. Rol actual: " . $_SESSION['rol']; exit(); }
if (!isset($_SESSION['carrito'])) { echo "Error: No existe la variable de sesion carrito"; exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_producto'])) {
    $id_producto   = $_POST['id_producto'];
    $nombre_producto = $_POST['nombre_producto'];
    $precio_final  = $_POST['precio_final'];
    $cantidad      = (int)$_POST['cantidad'];

    $encontrado = false;

    // Buscamos si el producto ya está en el carrito
    foreach ($_SESSION['carrito']['productos'] as &$item) {
        $itemEsRecompensa = !empty($item['es_recompensa']);

        if ($item['id_producto'] == $id_producto && !$itemEsRecompensa) {
            $item['cantidad'] += $cantidad;
            $encontrado = true;
            break;
        }
    }
    unset($item);

    // Si no estaba, lo añadimos
    if (!$encontrado) {
        $_SESSION['carrito']['productos'][] = [
            'id_producto'    => $id_producto,
            'nombre'         => $nombre_producto,
            'precio'         => $precio_final,
            'cantidad'       => $cantidad,
            'es_recompensa'  => false
        ];
    }

    // Calculamos el nuevo total de artículos
    $cantidadTotalCarrito = 0;
    foreach ($_SESSION['carrito']['productos'] as $item) {
        $cantidadTotalCarrito += $item['cantidad'];
    }

    // Devolvemos el número
    echo $cantidadTotalCarrito;
    exit();
}

echo "Error: No llegaron los datos por POST";
?>