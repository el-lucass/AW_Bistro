<?php
// Subimos un nivel para encontrar el autoloader
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

// Protección de acceso: solo el gerente puede crear productos
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = "Añadir Nuevo Producto";
// Obtenemos las categorías llamando al método estático de Producto
$categorias = Producto::listaCategorias();
$opcionesCategoria = "";

if (empty($categorias)) {
    $opcionesCategoria = "<option value=''>-- No hay categorías disponibles --</option>";
} else {
    foreach ($categorias as $cat) {
        $opcionesCategoria .= "<option value='{$cat['id']}'>{$cat['nombre']}</option>";
    }
}

// Gestión de mensajes (éxito o error)
$mensaje = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'db') {
        $mensaje = "<div class='alerta-error'>Error al guardar el producto en la base de datos.</div>";
    } elseif ($_GET['error'] === 'img') {
        $mensaje = "<div class='alerta-warning'>El producto se guardó, pero hubo un error al subir alguna imagen.</div>";
    }
}

// Importante: enctype="multipart/form-data" es obligatorio para poder subir archivos (imágenes)
$contenidoPrincipal = <<<EOS
<h1>Añadir nuevo producto a la carta</h1>
{$mensaje}
<form id="formCrearProducto" action="../productos/admin_crear_producto.php" method="POST" enctype="multipart/form-data">
    <fieldset>
        <legend>Datos básicos del producto</legend>
        <div class="form-div">
            <label>Nombre del producto:</label>
            <input type="text" name="nombre" required class="input-full">
        </div>
        <div class="form-div">
            <label>Descripción:</label>
            <textarea name="descripcion" required rows="4"></textarea>
        </div>
        <div class="form-div">
            <label>Categoría:</label>
            <select name="id_categoria" required>{$opcionesCategoria}</select>
        </div>
    </fieldset>

    <fieldset class="fieldset-mt">
        <legend>Precios y Disponibilidad</legend>
        <div class="form-div">
            <label>Precio Base (sin IVA) en €:</label>
            <input type="number" name="precio_base" id="precio_base" step="0.01" min="0" required>
        </div>
        <div class="form-div">
            <label>IVA Aplicable:</label>
            <select name="iva" id="iva" required>
                <option value="4">4% (Superreducido)</option>
                <option value="10" selected>10% (Reducido - Hostelería)</option>
                <option value="21">21% (General)</option>
            </select>
        </div>
        <div class="precio-calculado-box">
            <span class="precio-calculado-label">
                <strong>Precio Final de Venta:
                    <span id="precio_final_display" class="precio-calculado-valor">0.00 €</span>
                </strong>
            </span>
        </div>
        <div class="form-div">
            <label>
                <input type="checkbox" name="disponible" value="1" checked>
                El producto está disponible actualmente (hay stock para prepararlo)
            </label>
        </div>
    </fieldset>

    <fieldset class="fieldset-mt">
        <legend>Imágenes (Opcional)</legend>
        <p class="texto-sm texto-gris">Puedes seleccionar varias imágenes. La primera se usará como portada.</p>
        <input type="file" id="imagenes_input" name="imagenes[]" accept="image/jpeg, image/png, image/webp" multiple>
        <div id="preview-imagenes"></div>
    </fieldset>

    <div class="mt-15">
        <button type="submit" class="btn-admin btn-lg">Guardar Producto</button>
        <a href="productos.php" class="ml-10">
            <button type="button" class="btn-secundario btn-lg">Cancelar</button>
        </a>
    </div>
</form>

<script>
function actualizarPrecioFinal() {
    let base = parseFloat(document.getElementById('precio_base').value) || 0;
    let iva  = parseFloat(document.getElementById('iva').value) || 0;
    let total = base + (base * (iva / 100));
    document.getElementById('precio_final_display').innerText = total.toFixed(2) + ' €';
}
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('precio_base').addEventListener('input', actualizarPrecioFinal);
    document.getElementById('iva').addEventListener('change', actualizarPrecioFinal);
    actualizarPrecioFinal();

    activarValidacion('formCrearProducto', {
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
</script>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>