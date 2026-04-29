<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

// Seguridad: Solo el gerente puede editar
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
// Buscamos el producto usando el método estático (devuelve un Objeto Producto)
$producto = Producto::buscaProducto($id);

if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}

$tituloPagina = "Editar Producto: " . htmlspecialchars($producto->getNombre());

// Selector de categorías
$categorias = Producto::listaCategorias();
$opcionesCategoria = "";
foreach ($categorias as $cat) {
    $selected = ($cat['id'] == $producto->getIdCategoria()) ? "selected" : "";
    $opcionesCategoria .= "<option value='{$cat['id']}' {$selected}>{$cat['nombre']}</option>";
}

// Selector de IVA
$ivaOpciones = "";
foreach ([4, 10, 21] as $tipoIva) {
    $selected = ($producto->getIva() == $tipoIva) ? "selected" : "";
    $ivaOpciones .= "<option value='{$tipoIva}' {$selected}>{$tipoIva}%</option>";
}

$checkedDisponible = $producto->getDisponible() ? "checked" : "";

$galeriaHTML = "<div class='galeria-imagenes'>";
$imagenesActuales = $producto->getImagenes();
if (!empty($imagenesActuales)) {
    foreach ($imagenesActuales as $img) {
        $galeriaHTML .= "
        <div class='galeria-item'>
            <img src='../img/productos/{$img['ruta_imagen']}' width='80' height='80'>
            <label class='galeria-borrar'>
                <input type='checkbox' name='eliminar_imagenes[]' value='{$img['id']}'> Borrar
            </label>
        </div>";
    }
} else {
    $galeriaHTML .= "<p class='galeria-vacia'>No hay imágenes asociadas.</p>";
}
$galeriaHTML .= "</div>";

// Mensaje de error si lo hay
$mensaje = "";
if (isset($_GET['error']) && $_GET['error'] === 'db') {
    $mensaje = "<div class='alerta-error'>Error al actualizar en la base de datos.</div>";
}

$contenidoPrincipal = "
<h1>Editar Producto</h1>
{$mensaje}
<form id='formEditarProducto' action='../productos/admin_editar_producto.php' method='POST' enctype='multipart/form-data'>
    <input type='hidden' name='id' value='{$producto->getId()}'>

    <fieldset>
        <legend>Datos básicos</legend>
        <div class='form-div'>
            <label>Nombre:</label>
            <input type='text' name='nombre' value='" . htmlspecialchars($producto->getNombre()) . "' required class='input-full'>
        </div>
        <div class='form-div'>
            <label>Descripción:</label>
            <textarea name='descripcion' required rows='4'>" . htmlspecialchars($producto->getDescripcion()) . "</textarea>
        </div>
        <div class='form-div'>
            <label>Categoría:</label>
            <select name='id_categoria' required>{$opcionesCategoria}</select>
        </div>
    </fieldset>

    <fieldset class='fieldset-mt'>
        <legend>Precios y Disponibilidad</legend>
        <div class='form-div'>
            <label>Precio Base (€):</label>
            <input type='number' name='precio_base' id='precio_base' value='{$producto->getPrecioBase()}' step='0.01' min='0' required>
        </div>
        <div class='form-div'>
            <label>IVA:</label>
            <select name='iva' id='iva' required>{$ivaOpciones}</select>
        </div>
        <div class='precio-calculado-box'>
            <span class='precio-calculado-label'>
                <strong>Precio Final de Venta:
                    <span id='precio_final_display' class='precio-calculado-valor'>0.00 €</span>
                </strong>
            </span>
        </div>
        <div class='form-div'>
            <label>
                <input type='checkbox' name='disponible' value='1' {$checkedDisponible}>
                Disponible (hay stock)
            </label>
        </div>
    </fieldset>

    <fieldset class='fieldset-mt'>
        <legend>Galería de Imágenes</legend>
        <p><strong>Imágenes actuales:</strong></p>
        {$galeriaHTML}
        <p class='mt-15'><strong>Añadir MÁS imágenes:</strong></p>
        <input type='file' id='imagenes_input' name='imagenes[]' accept='image/*' multiple>
        <div id='preview-imagenes'></div>
    </fieldset>

    <div class='mt-15'>
        <button type='submit' class='btn-naranja btn-lg'>Actualizar Producto</button>
        <a href='productos.php' class='ml-10'>
            <button type='button' class='btn-secundario btn-lg'>Cancelar</button>
        </a>
    </div>
</form>

<script>
function actualizarPrecioFinal() {
    let base  = parseFloat(document.getElementById('precio_base').value) || 0;
    let iva   = parseFloat(document.getElementById('iva').value) || 0;
    let total = base + (base * (iva / 100));
    document.getElementById('precio_final_display').innerText = total.toFixed(2) + ' €';
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('precio_base').addEventListener('input', actualizarPrecioFinal);
    document.getElementById('iva').addEventListener('change', actualizarPrecioFinal);
    actualizarPrecioFinal();

    activarValidacion('formEditarProducto', {
        nombre:        ['requerido', ['minLen', 2], ['maxLen', 100]],
        descripcion:   ['requerido', ['minLen', 5]],
        id_categoria:  ['requerido'],
        precio_base:   ['requerido', 'numeroPositivo'],
        'imagenes[]':  [['imagenes', 5, 2]]
    });
    previsualizarImagenes(
        document.getElementById('imagenes_input'),
        document.getElementById('preview-imagenes')
    );
});
</script>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
