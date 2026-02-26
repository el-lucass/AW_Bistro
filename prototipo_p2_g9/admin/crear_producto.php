<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// Protección de acceso: solo el gerente puede crear productos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = "Añadir Nuevo Producto";

// Obtenemos las categorías de la base de datos para rellenar el selector (dropdown)
$categorias = listaCategorias();
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
        $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;'>Error al guardar el producto en la base de datos.</div>";
    } elseif ($_GET['error'] === 'img') {
        $mensaje = "<div style='background: #fff3cd; color: #856404; padding: 10px; margin-bottom: 15px;'>El producto se guardó, pero hubo un error al subir alguna imagen.</div>";
    }
}

// Importante: enctype="multipart/form-data" es obligatorio para poder subir archivos (imágenes)
$contenidoPrincipal = <<<EOS
    <h1>Añadir nuevo producto a la carta</h1>
    {$mensaje}
    <form action="../productos/admin_crear_producto.php" method="POST" enctype="multipart/form-data">
        <fieldset>
            <legend>Datos básicos del producto</legend>
            
            <div style="margin-bottom: 10px;">
                <label>Nombre del producto:</label> 
                <input type="text" name="nombre" required style="width: 100%;">
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Descripción:</label><br>
                <textarea name="descripcion" required rows="4" style="width: 100%;"></textarea>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>Categoría:</label>
                <select name="id_categoria" required>
                    {$opcionesCategoria}
                </select>
            </div>
        </fieldset>
        
        <fieldset style="margin-top:10px;">
            <legend>Precios y Disponibilidad</legend>
            
            <div style="margin-bottom: 10px;">
                <label>Precio Base (sin IVA) en €:</label> 
                <input type="number" name="precio_base" id="precio_base" step="0.01" min="0" required>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>IVA Aplicable:</label>
                <select name="iva" id="iva" required>
                    <option value="4">4% (Superreducido)</option>
                    <option value="10" selected>10% (Reducido - Hostelería)</option>
                    <option value="21">21% (General)</option>
                </select>
            </div>

            <div style="margin-bottom: 15px; padding: 10px; background-color: #ecf0f1; border-radius: 5px;">
                <span style="font-size: 1.1em; color: #2c3e50;">
                    <strong>Precio Final de Venta: <span id="precio_final_display" style="color: #27ae60; font-size: 1.2em;">0.00 €</span></strong>
                </span>
            </div>
            
            <div style="margin-bottom: 10px;">
                <label>
                    <input type="checkbox" name="disponible" value="1" checked>
                    El producto está disponible actualmente (hay stock para prepararlo)
                </label>
            </div>
        </fieldset>

        <fieldset style="margin-top:10px;">
            <legend>Imágenes (Opcional pero recomendado)</legend>
            <p style="font-size: 0.9em; color: #666;">Puedes seleccionar varias imágenes a la vez manteniendo pulsada la tecla Ctrl/Cmd. La primera imagen se usará como portada.</p>
            <input type="file" name="imagenes[]" accept="image/jpeg, image/png, image/webp" multiple>
        </fieldset>

        <button type="submit" style="margin-top:15px; padding:10px 20px; background:#d35400; color:white; border:none; cursor: pointer;">
            Guardar Producto
        </button>
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
        actualizarPrecioFinal(); // Calcula el 0 inicial
    });
    </script>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>