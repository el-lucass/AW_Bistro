<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Categorías';
$categorias = listaCategorias();

// Mensajes de éxito o error
$mensaje = "";
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>¡Categoría creada correctamente!</div>";
} elseif (isset($_GET['error'])) {
    $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;'>Error al crear la categoría. Revisa los datos.</div>";
}

// Generamos la tabla de categorías
$tabla = "<table border='1' style='width:100%; text-align:left; border-collapse: collapse;'>
    <tr style='background-color: #f2f2f2;'>
        <th style='padding: 10px;'>ID</th>
        <th style='padding: 10px;'>Nombre</th>
        <th style='padding: 10px;'>Descripción</th>
    </tr>";

if (!empty($categorias)) {
    foreach ($categorias as $cat) {
        $tabla .= "<tr>
            <td style='padding: 10px;'>{$cat['id']}</td>
            <td style='padding: 10px;'><strong>{$cat['nombre']}</strong></td>
            <td style='padding: 10px;'>{$cat['descripcion']}</td>
        </tr>";
    }
} else {
    $tabla .= "<tr><td colspan='3' style='padding: 10px; text-align:center;'>No hay categorías creadas. Usa el formulario para crear la primera.</td></tr>";
}
$tabla .= "</table>";

// Construimos el HTML de la página usando un layout de 2 columnas (Flexbox)
$contenidoPrincipal = "
    <h1>Gestión de Categorías</h1>
    {$mensaje}
    
    <div style='display: flex; gap: 30px; align-items: flex-start; margin-top: 20px;'>
        
        <div style='flex: 2;'>
            <h2>Categorías Existentes</h2>
            $tabla
        </div>
        
        <div style='flex: 1; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;'>
            <h2 style='margin-top: 0;'>Añadir Nueva</h2>
            <form action='../productos/admin_crear_categoria.php' method='POST'>
                <div style='margin-bottom: 15px;'>
                    <label>Nombre de la Categoría:</label><br>
                    <input type='text' name='nombre' required style='width: 100%; padding: 5px;' placeholder='Ej: Bebidas'>
                </div>
                
                <div style='margin-bottom: 15px;'>
                    <label>Descripción:</label><br>
                    <textarea name='descripcion' required rows='4' style='width: 100%; padding: 5px;' placeholder='Ej: Refrescos, cervezas y agua...'></textarea>
                </div>
                
                <button type='submit' style='background-color:#2980b9; color:white; border:none; padding:10px; width: 100%; cursor:pointer; font-weight:bold; border-radius: 5px;'>
                    Crear Categoría
                </button>
            </form>
        </div>
        
    </div>
    
    <div style='margin-top: 30px;'>
        <a href='productos.php'>
            <button style='background-color:#7f8c8d; color:white; padding:10px; cursor:pointer; border:none; border-radius:5px;'>
                ← Volver a Productos
            </button>
        </a>
    </div>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>