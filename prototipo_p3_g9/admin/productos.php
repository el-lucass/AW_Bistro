<?php
// Subimos un nivel para encontrar la configuración y el autoloader
require_once '../includes/config.php';

// Importamos las clases que vamos a usar
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

// Verificación de seguridad usando el método estático de Usuario
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Productos - Bistro FDI';

// Obtenemos todos los productos (pasamos 'false' para que traiga también los retirados/borrados)
// ¡Ahora esto nos devuelve un array de OBJETOS Producto!
$productos = Producto::listaProductos(false);

// Gestión de mensajes de estado
$mensaje = "";
if (isset($_GET['status'])) {
    $msgs = [
        'deleted'  => 'Producto retirado de la carta correctamente.',
        'restored' => 'Producto añadido de vuelta a la carta.',
        'updated'  => 'Producto actualizado correctamente.',
        'created'  => '¡Producto creado y añadido a la carta correctamente!',
    ];
    if (isset($msgs[$_GET['status']])) {
        $mensaje = "<div class='alerta-exito'>" . $msgs[$_GET['status']] . "</div>";
    }
}

$tabla = '<table>
    <thead><tr>
        <th>ID</th><th class="texto-centro">Foto</th><th>Nombre</th><th>Categoría</th>
        <th>Precio Final</th><th>Stock</th><th>Estado</th><th>Acciones</th>
    </tr></thead>
    <tbody>';

if (!empty($productos)) {
    foreach ($productos as $prod) {
        $id          = $prod->getId();
        $nombre      = htmlspecialchars($prod->getNombre());
        $categoria   = htmlspecialchars($prod->getNombreCategoria());
        $precioFinal = $prod->getPrecioTotal();
        $disponible  = $prod->getDisponible();
        $ofertado    = $prod->getOfertado();

        $rutaImg = $prod->getImagenPrincipal()
            ? "../img/productos/" . $prod->getImagenPrincipal()
            : "../img/productos/default_food.png";

        $precioFmt  = number_format($precioFinal, 2, ',', '.') . " €";
        $precioBase = $prod->getPrecioBase();
        $iva        = $prod->getIva();

        $stockBadge  = $disponible ? "<span class='stock-disponible'>Disponible</span>" : "<span class='stock-agotado'>Agotado</span>";
        $estadoBadge = $ofertado   ? "<span class='carta-activa'>En Carta</span>"       : "<span class='carta-retirada'>Retirado</span>";

        $accion   = $ofertado ? 'retirar'    : 'restaurar';
        $claseBtn = $ofertado ? 'btn-peligro' : 'btn-exito';
        $textoBtn = $ofertado ? 'Retirar'    : 'Restaurar';

        $tabla .= "<tr>
            <td>{$id}</td>
            <td class='texto-centro'><img src='{$rutaImg}' width='50' height='50' class='avatar-mini'></td>
            <td><strong>{$nombre}</strong></td>
            <td>{$categoria}</td>
            <td>{$precioFmt}<br><small class='texto-gris'>(Base: {$precioBase}€ + {$iva}% IVA)</small></td>
            <td>{$stockBadge}</td>
            <td>{$estadoBadge}</td>
            <td>
                <a href='editar_producto.php?id={$id}'>
                    <button class='btn-editar btn-sm mb-5'>Editar</button>
                </a>
                <form action='../productos/admin_borrar_producto.php' method='POST' class='inline'>
                    <input type='hidden' name='id' value='{$id}'>
                    <input type='hidden' name='accion' value='{$accion}'>
                    <button type='submit' class='{$claseBtn} btn-sm'>{$textoBtn}</button>
                </form>
            </td>
        </tr>";
    }
} else {
    $tabla .= "<tr><td colspan='8' class='texto-centro'>No hay productos registrados.</td></tr>";
}
$tabla .= '</tbody></table>';

$contenidoPrincipal = "
<h1>Panel de Control: Gestión de Productos</h1>
{$mensaje}
<div class='admin-toolbar'>
    <a href='crear_producto.php'><button class='btn-crear btn-lg'>+ Añadir Nuevo Producto</button></a>
    <a href='categorias.php'><button class='btn-azul btn-lg'>📁 Gestionar Categorías</button></a>
</div>
{$tabla}";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
