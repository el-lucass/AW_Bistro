<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
$producto = buscaProducto($id);

if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}

$tituloPagina = "Editar Producto: " . htmlspecialchars($producto['nombre']);

// Selector de categorías
$categorias = listaCategorias();
$opcionesCategoria = "";
foreach ($categorias as $cat) {
    $selected = ($cat['id'] == $producto['id_categoria']) ? "selected" : "";
    $opcionesCategoria .= "<option value='{$cat['id']}' $selected>{$cat['nombre']}</option>";
}

// Selector de IVA
$ivaOpciones = "";
foreach ([4, 10, 21] as $tipoIva) {
    $selected = ($producto['iva'] == $tipoIva) ? "selected" : "";
    $ivaOpciones .= "<option value='$tipoIva' $selected>$tipoIva%</option>";
}

$checkedDisponible = $producto['disponible'] ? "checked" : "";

// Mostrar imágenes actuales
$galeriaHTML = "<div style='display:flex; gap: 10px; margin-bottom: 10px;'>";
if (!empty($producto['imagenes'])) {
    foreach ($producto['imagenes'] as $img) {
        $galeriaHTML .= "<img src='../img/productos/{$img['ruta_imagen']}' width='80' height='80' style='object-fit:cover; border-radius: 5px; border: 1px solid #ccc;'>";
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
        <input type='hidden' name='id' value='{$producto['id']}'>
        
        <fieldset>
            <legend>Datos básicos</legend>
            <div style='margin-bottom: 10px;'>
                <label>Nombre:</label> 
                <input type='text' name='nombre' value='" . htmlspecialchars($producto['nombre']) . "' required style='width: 100%;'>
            </div>
            
            <div style='margin-bottom: 10px;'>
                <label>Descripción:</label><br>
                <textarea name='descripcion' required rows='4' style='width: 100%;'>" . htmlspecialchars($producto['descripcion']) . "</textarea>
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
                <input type='number' name='precio_base' value='{$producto['precio_base']}' step='0.01' min='0' required>
            </div>
            
            <div style='margin-bottom: 10px;'>
                <label>IVA:</label>
                <select name='iva' required>
                    {$ivaOpciones}
                </select>
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
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>