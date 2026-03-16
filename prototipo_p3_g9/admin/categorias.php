<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\productos\Producto;

// Seguridad: Solo el gerente puede gestionar categorías
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Categorías';

// LLAMADA ACTUALIZADA: Usamos el método estático de la clase Producto
$categorias = Producto::listaCategoriasConConteo();

// Mensajes
$mensaje = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>¡Categoría creada correctamente!</div>";
    } elseif ($_GET['status'] === 'deleted') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>Categoría eliminada correctamente.</div>";
    } elseif ($_GET['status'] === 'updated') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>Categoría actualizada correctamente.</div>";
    }
} elseif (isset($_GET['error'])) {
    $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px;'>Error al procesar la categoría. Revisa los datos.</div>";
}

// Generamos la tabla
$tabla = "<table border='1' style='width:100%; text-align:left; border-collapse: collapse;'>
    <tr style='background-color: #f2f2f2;'>
        <th style='padding: 10px;'>ID</th>
        <th style='padding: 10px;'>Nombre</th>
        <th style='padding: 10px;'>Descripción</th>
        <th style='padding: 10px; text-align:center;'>Productos Asociados</th>
        <th style='padding: 10px;'>Acciones</th>
    </tr>";

if (!empty($categorias)) {
    foreach ($categorias as $cat) {
        $total = $cat['total_productos'];
        
        $badge = $total > 0 
            ? "<span style='color: #2980b9; font-weight: bold;'>{$total} productos</span>"
            : "<span style='color: #7f8c8d; font-style: italic;'>0 productos</span>";
            
        $tabla .= "<tr>";
        $tabla .= "<td style='padding: 10px;'>{$cat['id']}</td>";
        $tabla .= "<td style='padding: 10px;'><strong>" . htmlspecialchars($cat['nombre']) . "</strong></td>";
        $tabla .= "<td style='padding: 10px;'>" . htmlspecialchars($cat['descripcion']) . "</td>";
        $tabla .= "<td style='padding: 10px; text-align:center;'>{$badge}</td>";
        
        $tabla .= "<td style='padding: 10px;'>";
        $tabla .= "<div style='display: flex; gap: 5px; align-items: center;'>";
        
        $tabla .= "<a href='editar_categoria.php?id={$cat['id']}' style='text-decoration: none;'>
                        <button type='button' style='background-color:#f39c12; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius: 3px;'>Editar</button>
                   </a>";
            
        if ($total == 0) {
            $tabla .= "<form action='../productos/admin_borrar_categoria.php' method='POST' style='margin: 0;' onsubmit='return confirm(\"¿Seguro que quieres borrar esta categoría?\")'>
                            <input type='hidden' name='id' value='{$cat['id']}'>
                            <button type='submit' style='background-color:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius: 3px;'>Borrar</button>
                       </form>";
        } else {
            $tabla .= "<button type='button' onclick='alert(\"No se puede borrar esta categoría porque contiene productos.\\n\\nPor favor, cambia de categoría los productos asociados primero.\");' style='background-color:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius: 3px;'>Borrar</button>";
        }
        
        $tabla .= "</div>";
        $tabla .= "</td>";
        $tabla .= "</tr>";
    }
} else {
    $tabla .= "<tr><td colspan='5' style='padding: 10px; text-align:center;'>No hay categorías creadas.</td></tr>";
}
$tabla .= "</table>";

$contenidoPrincipal = "
    <h1>Gestión de Categorías</h1>
    {$mensaje}
    
    <div style='margin-bottom: 20px; display: flex; gap: 15px;'>
        <a href='crear_categoria.php'>
            <button style='background-color:#2ecc71; color:white; padding:10px 15px; cursor:pointer; border:none; border-radius:5px; font-weight:bold;'>
                + Añadir Nueva Categoría
            </button>
        </a>
        <a href='productos.php'>
            <button style='background-color:#7f8c8d; color:white; padding:10px 15px; cursor:pointer; border:none; border-radius:5px; font-weight:bold;'>
                ← Volver a Productos
            </button>
        </a>
    </div>
    
    <div>
        $tabla
    </div>
";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';