<?php
require_once '../includes/config.php';
require_once '../includes/productos.php';

// Seguridad
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
$categoria = buscaCategoria($id);

if (!$categoria) {
    echo "Categoría no encontrada.";
    exit;
}

$tituloPagina = "Editar Categoría: " . htmlspecialchars($categoria['nombre']);

// Mensaje de error si lo hay
$mensaje = "";
if (isset($_GET['error'])) {
    $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;'>Error al actualizar la categoría. Revisa los datos.</div>";
}

$contenidoPrincipal = "
    <h1>Editar Categoría</h1>
    {$mensaje}
    
    <form action='../productos/admin_editar_categoria.php' method='POST' style='max-width: 600px;'>
        <input type='hidden' name='id' value='{$categoria['id']}'>
        
        <fieldset>
            <legend>Datos de la Categoría</legend>
            <div style='margin-bottom: 15px;'>
                <label>Nombre de la Categoría:</label><br>
                <input type='text' name='nombre' value='" . htmlspecialchars($categoria['nombre']) . "' required style='width: 100%; padding: 5px;'>
            </div>
            
            <div style='margin-bottom: 15px;'>
                <label>Descripción:</label><br>
                <textarea name='descripcion' required rows='4' style='width: 100%; padding: 5px;'>" . htmlspecialchars($categoria['descripcion']) . "</textarea>
            </div>
        </fieldset>
        
        <div style='margin-top: 15px;'>
            <button type='submit' style='padding:10px 20px; background:#e67e22; color:white; border:none; cursor: pointer; border-radius: 5px; font-weight: bold;'>
                Actualizar Categoría
            </button>
            <a href='categorias.php' style='margin-left: 10px; text-decoration: none;'>
                <button type='button' style='padding:10px 20px; background:#7f8c8d; color:white; border:none; cursor: pointer; border-radius: 5px;'>Cancelar</button>
            </a>
        </div>
    </form>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>