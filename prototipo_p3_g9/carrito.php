<?php
require_once __DIR__.'/includes/config.php';

// Importamos la clase Usuario
use es\ucm\fdi\aw\usuarios\Usuario;

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
    foreach ($_SESSION['carrito']['productos'] as $key => $item) {
        if ($item['id_producto'] == $id_a_eliminar) {
            unset($_SESSION['carrito']['productos'][$key]);
            break;
        }
    }
    // Reindexar el array para evitar huecos
    $_SESSION['carrito']['productos'] = array_values($_SESSION['carrito']['productos']);
    header('Location: carrito.php');
    exit();
}

// Lógica para actualizar cantidades 
if (isset($_GET['actualizar']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $op = $_GET['actualizar']; 
    
    foreach ($_SESSION['carrito']['productos'] as &$item) {
        if ($item['id_producto'] == $id) {
            if ($op == 'sumar') {
                $item['cantidad']++;
            } elseif ($op == 'restar') {
                $item['cantidad']--;
                if ($item['cantidad'] <= 0) {
                     header("Location: carrito.php?eliminar={$id}");
                     exit();
                }
            }
            break;
        }
    }
    header('Location: carrito.php');
    exit();
}

$tituloPagina = 'Carrito de Compra - Bistro FDI';
$contenidoPrincipal = "<div style='padding: 20px;'>";

// BOTÓN DE VOLVER 
$botonVolver = "
<div style='margin-bottom: 20px;'>
    <a href='catalogo.php' style='text-decoration: none;'>
        <button style='background-color: white; color: #333; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer; transition: 0.2s;'>
            ← Volver al catálogo
        </button>
    </a>
</div>";

// Verificamos si hay un carrito activo
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    $contenidoPrincipal .= $botonVolver;
    $contenidoPrincipal .= "
    <h1>Carrito de Compra</h1>
    <p>Tu carrito está vacío en este momento.</p>
    </div>";
} else {
    $tipoTexto = ($_SESSION['carrito']['tipo'] == 'local') ? 'Consumir en Local' : 'Para Llevar';
    
    $contenidoPrincipal .= $botonVolver;
    $contenidoPrincipal .= "
    <h1 style='margin-top: 0; margin-bottom: 5px;'>Carrito de Compra</h1>
    <p style='color: #666; margin-top: 0; margin-bottom: 30px;'>Tipo: {$tipoTexto}</p>
    ";

    // --- TABLA DEL CARRITO ---
    $contenidoPrincipal .= "
    <table style='width: 100%; border-collapse: collapse; margin-bottom: 30px;'>
        <thead>
            <tr style='border-bottom: 1px solid #ccc; text-align: left;'>
                <th style='padding: 15px 10px;'>Producto</th>
                <th style='padding: 15px 10px; text-align: center;'>Precio</th>
                <th style='padding: 15px 10px; text-align: center;'>Cantidad</th>
                <th style='padding: 15px 10px; text-align: right;'>Subtotal</th>
                <th style='padding: 15px 10px;'></th>
            </tr>
        </thead>
        <tbody>
    ";

    $totalCarrito = 0;

    foreach ($_SESSION['carrito']['productos'] as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $totalCarrito += $subtotal;

        $precioFormateado = number_format($item['precio'], 2);
        $subtotalFormateado = number_format($subtotal, 2);

        $contenidoPrincipal .= "
            <tr style='border-bottom: 1px solid #eee;'>
                <td style='padding: 20px 10px;'>
                    <strong style='font-size: 16px;'>" . htmlspecialchars($item['nombre']) . "</strong>
                </td>
                <td style='padding: 20px 10px; text-align: center;'>{$precioFormateado} €</td>
                <td style='padding: 20px 10px; text-align: center;'>
                    <div style='display: flex; align-items: center; justify-content: center; gap: 10px;'>
                        <a href='carrito.php?actualizar=restar&id={$item['id_producto']}' style='text-decoration: none;'>
                            <button style='background: white; border: 1px solid #ccc; width: 30px; height: 30px; cursor: pointer; border-radius: 3px;'>-</button>
                        </a>
                        <span style='font-size: 16px; width: 20px; text-align: center;'>{$item['cantidad']}</span>
                        <a href='carrito.php?actualizar=sumar&id={$item['id_producto']}' style='text-decoration: none;'>
                            <button style='background: white; border: 1px solid #ccc; width: 30px; height: 30px; cursor: pointer; border-radius: 3px;'>+</button>
                        </a>
                    </div>
                </td>
                <td style='padding: 20px 10px; text-align: right;'>{$subtotalFormateado} €</td>
                <td style='padding: 20px 10px; text-align: center;'>
                    <a href='carrito.php?eliminar={$item['id_producto']}' style='text-decoration: none;'>
                        <button style='background: white; border: 1px solid #ccc; padding: 5px 10px; cursor: pointer; border-radius: 3px;'>🗑️</button>
                    </a>
                </td>
            </tr>
        ";
    }

    $contenidoPrincipal .= "
        </tbody>
    </table>
    ";

    $totalFormateado = number_format($totalCarrito, 2);

    // --- TOTAL ---
    $contenidoPrincipal .= "
    <div style='background-color: #f9f9f9; border: 1px solid #eee; padding: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; border-radius: 5px;'>
        <h2 style='margin: 0; font-size: 20px;'>Total (IVA incluido):</h2>
        <h2 style='margin: 0; font-size: 20px;'>{$totalFormateado} €</h2>
    </div>
    ";

    // --- BOTONES DE ACCIÓN
    $contenidoPrincipal .= "
    <div style='display: flex; gap: 15px; justify-content: flex-end;'>
        <a href='carrito.php?accion=vaciar' style='padding: 10px 20px; font-size: 14px; background: white; border: 1px solid black; color: black; text-decoration: none; cursor: pointer; border-radius: 5px; text-align: center;'>
            Cancelar Pedido
        </a>
        
        <a href='pago.php' style='padding: 10px 30px; font-size: 14px; background: black; color: white; text-decoration: none; border: none; cursor: pointer; border-radius: 5px; text-align: center;'>
            Confirmar y Pagar
        </a>
    </div>
    ";

    $contenidoPrincipal .= "</div>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php'; 
?>