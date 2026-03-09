<?php
require_once '../includes/config.php';

// Importamos la clase Usuario para la comprobación de seguridad
use es\ucm\fdi\aw\Usuario;

// Seguridad: Solo el gerente puede acceder
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = "Añadir Nueva Categoría";

$mensaje = "";
if (isset($_GET['error'])) {
    $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;'>Error al crear la categoría. Revisa los datos.</div>";
}

$contenidoPrincipal = "
    <h1>Añadir Nueva Categoría</h1>
    {$mensaje}
    
    <form action='../productos/admin_crear_categoria.php' method='POST' style='max-width: 600px;'>
        <fieldset>
            <legend>Datos de la Categoría</legend>
            <div style='margin-bottom: 15px;'>
                <label>Nombre de la Categoría:</label><br>
                <input type='text' name='nombre' required style='width: 100%; padding: 5px;' placeholder='Ej: Bebidas'>
            </div>
            
            <div style='margin-bottom: 15px;'>
                <label>Descripción:</label><br>
                <textarea name='descripcion' required rows='4' style='width: 100%; padding: 5px;' placeholder='Ej: Refrescos, cervezas y agua...'></textarea>
            </div>
        </fieldset>
        
        <div style='margin-top: 15px;'>
            <button type='submit' style='background-color:#2980b9; color:white; border:none; padding:10px 20px; cursor:pointer; font-weight:bold; border-radius: 5px;'>
                Guardar Categoría
            </button>
            <a href='categorias.php' style='margin-left: 10px; text-decoration: none;'>
                <button type='button' style='padding:10px 20px; background:#7f8c8d; color:white; border:none; cursor: pointer; border-radius: 5px;'>Cancelar</button>
            </a>
        </div>
    </form>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';