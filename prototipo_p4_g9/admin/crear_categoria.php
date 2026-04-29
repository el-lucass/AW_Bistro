<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = "Añadir Nueva Categoría";

$mensaje = "";
if (isset($_GET['error'])) {
    $mensaje = "<div class='alerta-error'>Error al crear la categoría. Revisa los datos.</div>";
}

$contenidoPrincipal = "
    <h1>Añadir Nueva Categoría</h1>
    {$mensaje}

    <form action='../productos/admin_crear_categoria.php' method='POST'>
        <fieldset>
            <legend>Datos de la Categoría</legend>
            <div class='mb-15'>
                <label>Nombre de la Categoría:</label>
                <input type='text' name='nombre' required placeholder='Ej: Bebidas'>
            </div>
            <div class='mb-15'>
                <label>Descripción:</label>
                <textarea name='descripcion' required rows='4' placeholder='Ej: Refrescos, cervezas y agua...'></textarea>
            </div>
        </fieldset>

        <div class='mt-15'>
            <button type='submit' class='btn-azul btn-lg'>Guardar Categoría</button>
            <a href='categorias.php' class='ml-10'>
                <button type='button' class='btn-secundario btn-lg'>Cancelar</button>
            </a>
        </div>
    </form>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
