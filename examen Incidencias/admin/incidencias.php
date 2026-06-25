<?php
require_once '../includes/config.php';

// Importamos las clases modernas
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\incidencias\Incidencia;


// Verificación de seguridad usando el método de clase
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Incidencias - Bistro FDI';

$listaIncidencias = Incidencia::listaIncidencias();

$tabla = '<table>
    <thead><tr>
        <th>ID del pedido</th><th>ID del usuario</th><th>Causas</th><th>Descripcion</th><th>Imagen</th><th>Estado</th><th>Acciones</th>
    </tr></thead>
    <tbody>';

if (!empty($listaIncidencias)) {
    foreach ($listaIncidencias as $row) {
        $id_pedido             = $row['id_pedido'];
        $id_usuario             = $row['id_usuario'];
        $causas             = $row['causas'];
        $descripcion             = $row['descripcion'];
        $imagen           = $row['imagen'];
        $estado           = $row['estado'];
        $imagen = $row['imagen'];

        $rutaImagen = "";
        if($imagen != ''){
            $rutaImagen   = "../img/incidencias/" . $imagen;
        }

        $accion = $estado == "pendiente" ? 'pendiente' : 'resuelta';
        $claseBtn = $estado == "pendiente" ? 'btn-peligro' : 'btn-exito';
        $textoBtn = $estado == "pendiente" ? 'Poner como incidencia resuelta'    : 'Poner como incidencia pendiente';
        $confirmMsg = "¿Seguro que quieres {$textoBtn}?";

        $tabla .= "<tr>
            <td>{$id_pedido}</td>
            <td>{$id_usuario}</td>
            <td>{$causas}</td>
            <td>{$descripcion}</td>
            <td><img src='$rutaImagen' width='30' height='30'></td>
            <td>{$estado}</td>
            <td>

                <form action='cambiar_incidencia.php' method='POST' class='inline' onsubmit=\"return confirm('{$confirmMsg}');\">
                    <input type='hidden' name='id_pedido' value='{$id_pedido}'>
                    <input type='hidden' name='accion' value='{$accion}'>
                    <button type='submit' class='{$claseBtn} btn-sm'>{$textoBtn}</button>
                </form>
            </td>";

        $tabla .= "</td></tr>";
    }
} else {
    $tabla .= "<tr><td colspan='6' class='texto-centro'>No hay incidencias registradas.</td></tr>";
}
$tabla .= '</tbody></table>';

$contenidoPrincipal = "
<h1>Panel de Control: Gestión de incidencias</h1>
{$tabla}";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
