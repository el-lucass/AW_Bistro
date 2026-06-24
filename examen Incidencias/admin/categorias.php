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
    $msgs = [
        'success' => '¡Categoría creada correctamente!',
        'deleted' => 'Categoría eliminada correctamente.',
        'updated' => 'Categoría actualizada correctamente.',
    ];
    if (isset($msgs[$_GET['status']])) {
        $mensaje = "<div class='alerta-exito'>" . $msgs[$_GET['status']] . "</div>";
    }
} elseif (isset($_GET['error'])) {
    $mensaje = "<div class='alerta-error'>Error al procesar la categoría. Revisa los datos.</div>";
}

$tabla = '<table>
    <thead><tr>
        <th>ID</th><th>Nombre</th><th>Descripción</th>
        <th class="texto-centro">Productos Asociados</th><th>Acciones</th>
    </tr></thead>
    <tbody>';

if (!empty($categorias)) {
    foreach ($categorias as $cat) {
        $total = $cat['total_productos'];
        $badge = $total > 0
            ? "<span class='texto-azul texto-negrita'>{$total} productos</span>"
            : "<span class='texto-gris texto-cursiva'>0 productos</span>";

        $tabla .= "<tr>
            <td>{$cat['id']}</td>
            <td><strong>" . htmlspecialchars($cat['nombre']) . "</strong></td>
            <td>" . htmlspecialchars($cat['descripcion']) . "</td>
            <td class='texto-centro'>{$badge}</td>
            <td>
                <div class='flex-fila gap-5'>
                    <a href='editar_categoria.php?id={$cat['id']}'>
                        <button class='btn-editar btn-sm'>Editar</button>
                    </a>";

        if ($total == 0) {
            $tabla .= "<form action='../productos/admin_borrar_categoria.php' method='POST' class='inline'
                              onsubmit='return confirm(\"¿Seguro que quieres borrar esta categoría?\")'>
                            <input type='hidden' name='id' value='{$cat['id']}'>
                            <button type='submit' class='btn-peligro btn-sm'>Borrar</button>
                       </form>";
        } else {
            $tabla .= "<button type='button' class='btn-peligro btn-sm'
                               onclick='alert(\"No se puede borrar esta categoría porque contiene productos.\\n\\nCambia de categoría los productos primero.\")'>
                           Borrar
                       </button>";
        }

        $tabla .= "</div></td></tr>";
    }
} else {
    $tabla .= "<tr><td colspan='5' class='texto-centro'>No hay categorías creadas.</td></tr>";
}
$tabla .= '</tbody></table>';

$contenidoPrincipal = "
<h1>Gestión de Categorías</h1>
{$mensaje}
<div class='admin-toolbar'>
    <a href='crear_categoria.php'><button class='btn-crear btn-lg'>+ Añadir Nueva Categoría</button></a>
    <a href='productos.php'><button class='btn-secundario btn-lg'>← Volver a Productos</button></a>
</div>
{$tabla}";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
