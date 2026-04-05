<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
$categoria = Producto::buscaCategoria($id);

if (!$categoria) {
    echo "Categoría no encontrada.";
    exit;
}

$tituloPagina = "Editar Categoría: " . htmlspecialchars($categoria['nombre']);

$mensaje = "";
if (isset($_GET['error'])) {
    $mensaje = "<div class='alerta-error'>Error al actualizar la categoría. Revisa los datos.</div>";
}

$contenidoPrincipal = "
    <h1>Editar Categoría</h1>
    {$mensaje}

    <form action='../productos/admin_editar_categoria.php' method='POST'>
        <input type='hidden' name='id' value='{$categoria['id']}'>

        <fieldset>
            <legend>Datos de la Categoría</legend>
            <div class='mb-15'>
                <label>Nombre de la Categoría:</label>
                <input type='text' name='nombre' value='" . htmlspecialchars($categoria['nombre']) . "' required>
            </div>
            <div class='mb-15'>
                <label>Descripción:</label>
                <textarea name='descripcion' required rows='4'>" . htmlspecialchars($categoria['descripcion']) . "</textarea>
            </div>
        </fieldset>

        <div class='mt-15'>
            <button type='submit' class='btn-naranja btn-lg'>Actualizar Categoría</button>
            <a href='categorias.php' class='ml-10'>
                <button type='button' class='btn-secundario btn-lg'>Cancelar</button>
            </a>
        </div>
    </form>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
