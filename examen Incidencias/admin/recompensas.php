<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\recompensas\Recompensa;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Recompensas - Bistro FDI';
$recompensas = Recompensa::listaRecompensas();

$tabla = "<table>
<thead>
<tr>
    <th>ID</th>
    <th>Producto</th>
    <th>BistroCoins</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>
</thead>
<tbody>";

if (!empty($recompensas)) {
    foreach ($recompensas as $r) {
        $id = $r->getId();
        $producto = htmlspecialchars($r->getNombreProducto());
        $coins = $r->getBistrocoins();
        $estado = $r->getActiva()
            ? "<span class='descuento-activo'>Activa</span>"
            : "<span class='descuento-inactivo'>Inactiva</span>";

        $tabla .= "
        <tr>
            <td>{$id}</td>
            <td><strong>{$producto}</strong></td>
            <td>{$coins} BistroCoins</td>
            <td>{$estado}</td>
            <td>
                <a href='editar_recompensa.php?id={$id}'>
                    <button class='btn-editar btn-sm mb-5'>Editar</button>
                </a>
                <form action='borrar_recompensa.php' method='POST' class='inline'
                      onsubmit='return confirm(\"¿Eliminar esta recompensa?\")'>
                    <input type='hidden' name='id' value='{$id}'>
                    <button type='submit' class='btn-peligro btn-sm'>Borrar</button>
                </form>
            </td>
        </tr>";
    }
} else {
    $tabla .= "<tr><td colspan='5' class='texto-centro'>No hay recompensas creadas.</td></tr>";
}

$tabla .= "</tbody></table>";

$contenidoPrincipal = "
<h1>Panel de Control: Gestión de Recompensas</h1>
<div class='admin-toolbar'>
    <a href='crear_recompensa.php'><button class='btn-crear btn-lg'>+ Nueva Recompensa</button></a>
</div>
{$tabla}";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>