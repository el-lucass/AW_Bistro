<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\Usuario;
use es\ucm\fdi\aw\Producto;

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
    $opcionesCategoria .= "<option value='{$cat['id']}' $selected>{$cat['nombre']}</option>";
}

// Selector de IVA
$ivaOpciones = "";
foreach ([4, 10, 21] as $tipoIva) {
    $selected = ($producto->getIva() == $tipoIva) ? "selected" : "";
    $ivaOpciones .= "<option value='$tipoIva' $selected>$tipoIva%</option>";
}

$checkedDisponible = $producto->getDisponible() ? "checked" : "";

// Mostrar imágenes actuales con opción a borrar
$galeriaHTML = "<div style='display:flex; gap: 15px; flex-wrap: wrap; margin-bottom: 10px;'>";
$imagenesActuales = $producto->getImagenes();

if (!empty($imagenesActuales)) {
    foreach ($imagenesActuales as $img) {
        $galeriaHTML .= "
        <div style='text-align: center; background: #fff; padding: 5px; border: 1px solid #ddd; border-radius: 5px;'>
            <img src='../img/productos/{$img['ruta_imagen']}' width='80' height='80' style='object-fit:cover; border-radius: 5px; display: block; margin-bottom: 5px;'>
            <label style='font-size: 0.85em; color: #c0392b; cursor: pointer;'>
                <input type='checkbox' name='eliminar_imagenes[]' value='{$img['id']}'> Borrar
            </label>
        </div>";
    }
} else {
    $galeriaHTML .= "<p style='color:#777;'>No hay imágenes asociadas.</p>";
}
$galeriaHTML .= "</div>";

// Mensaje de error si lo hay
$mensaje = "";
if (isset($_GET['error']) && $_GET['error'] === 'db') {
    $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;'>Error al actualizar en la base de datos.</div>";
}

$contenidoPrincipal = "
    <h1>Editar Producto</h1>
    {$mensaje}
    <form action='../productos/admin_editar_producto.php' method='POST' enctype='multipart/form-data'>
        <input type='hidden' name='id' value='{$producto->getId()}'>
        
        <fieldset>
            <legend>Datos básicos</legend>
            <div style='margin-bottom: 10px;'>
                <label>Nombre:</label> 
                <input type='text' name='nombre' value='" . htmlspecialchars($producto->getNombre()) . "' required style='width: 100%;'>
            </div>
            
            <div style='margin-bottom: 10px;'>
                <label>Descripción:</label><br>
                <textarea name='descripcion' required rows='4' style='width: 100%;'>" . htmlspecialchars($producto->getDescripcion()) . "</textarea>
            </div>
            
            <div style='margin-bottom: 10px;'>
                <label>Categoría:</label>
                <select name='id_categoria' required>
                    {$opcionesCategoria}
                </select>
            </div>
        </fieldset>
        
        <fieldset style='margin-top:10px;'>
            <legend>Precios y Disponibilidad</legend>
            
            <div style='margin-bottom: 10px;'>
                <label>Precio Base (€):</label> 
                <input type='number' name='precio_base' id='precio_base' value='{$producto->getPrecioBase()}' step='0.01' min='0' required>
            </div>
            
            <div style='margin-bottom: 10px;'>
                <label>IVA:</label>
                <select name='iva' id='iva' required>
                    {$ivaOpciones}
                </select>
            </div>
            
            <div style='margin-bottom: 15px; padding: 10px; background-color: #ecf0f1; border-radius: 5px;'>
                <span style='font-size: 1.1em; color: #2c3e50;'>
                    <strong>Precio Final de Venta: <span id='precio_final_display' style='color: #27ae60; font-size: 1.2em;'>0.00 €</span></strong>
                </span>
            </div>
            
            <div style='margin-bottom: 10px;'>
                <label>
                    <input type='checkbox' name='disponible' value='1' {$checkedDisponible}>
                    Disponible (hay stock)
                </label>
            </div>
        </fieldset>

        <fieldset style='margin-top:10px;'>
            <legend>Galería de Imágenes</legend>
            <p><strong>Imágenes actuales:</strong></p>
            {$galeriaHTML}
            
            <p style='margin-top: 15px;'><strong>Añadir MÁS imágenes:</strong></p>
            <input type='file' name='imagenes[]' accept='image/*' multiple>
        </fieldset>

        <button type='submit' style='margin-top:15px; padding:10px 20px; background:#e67e22; color:white; border:none; cursor: pointer;'>
            Actualizar Producto
        </button>
        <a href='productos.php' style='margin-left: 10px; text-decoration: none;'>
            <button type='button' style='padding:10px 20px; background:#7f8c8d; color:white; border:none; cursor: pointer;'>Cancelar</button>
        </a>
    </form>

    <script>
    function actualizarPrecioFinal() {
        let base = parseFloat(document.getElementById('precio_base').value) || 0;
        let iva = parseFloat(document.getElementById('iva').value) || 0;
        let total = base + (base * (iva / 100));
        document.getElementById('precio_final_display').innerText = total.toFixed(2) + ' €';
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('precio_base').addEventListener('input', actualizarPrecioFinal);
        document.getElementById('iva').addEventListener('change', actualizarPrecioFinal);
        actualizarPrecioFinal(); 
    });
    </script>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';