<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\Oferta;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Ofertas - Bistro FDI';
$ofertas      = Oferta::listaTodasLasOfertas();
$hoy          = new \DateTime();

$tabla = '<table>
    <thead><tr>
        <th>ID</th><th>Nombre</th><th>Descripción</th><th>Productos Requeridos</th>
        <th>Descuento</th><th>Disponibilidad</th><th>Estado</th><th>Acciones</th>
    </tr></thead>
    <tbody>';

if (!empty($ofertas)) {
    foreach ($ofertas as $oferta) {
        $id          = $oferta->getId();
        $nombre      = htmlspecialchars($oferta->getNombre());
        $descripcion = htmlspecialchars($oferta->getDescripcion());
        $descuento   = $oferta->getPorcentajeDescuento();
        $fechaInicio = (new \DateTime($oferta->getFechaInicio()))->format('d/m/Y');
        $fechaFin    = (new \DateTime($oferta->getFechaFin()))->format('d/m/Y');
        $activa      = ($hoy >= new \DateTime($oferta->getFechaInicio()) && $hoy <= new \DateTime($oferta->getFechaFin()));

        $productosHtml = "<ul class='lista-ul-compacta'>";
        foreach ($oferta->getProductos() as $prod) {
            $productosHtml .= "<li>" . htmlspecialchars($prod['nombre']) . " x{$prod['cantidad_requerida']}</li>";
        }
        $productosHtml .= "</ul>";

        $estadoHtml = $activa
            ? "<span class='descuento-activo'>Activa</span>"
            : "<span class='descuento-inactivo'>Inactiva</span>";

        $tabla .= "<tr>
            <td>{$id}</td>
            <td><strong>{$nombre}</strong></td>
            <td class='texto-sm texto-gris'>{$descripcion}</td>
            <td>{$productosHtml}</td>
            <td class='texto-negrita texto-azul'>{$descuento}%</td>
            <td class='texto-sm'>{$fechaInicio} — {$fechaFin}</td>
            <td>{$estadoHtml}</td>
            <td>
                <a href='editar_oferta.php?id={$id}'>
                    <button class='btn-editar btn-sm mb-5'>Editar</button>
                </a>
                <form action='borrar_oferta.php' method='POST' class='inline'
                      onsubmit='return confirm(\"¿Eliminar la oferta {$nombre}?\")'>
                    <input type='hidden' name='id' value='{$id}'>
                    <button type='submit' class='btn-peligro btn-sm'>Borrar</button>
                </form>
            </td>
        </tr>";
    }
} else {
    $tabla .= "<tr><td colspan='8' class='texto-centro'>No hay ofertas creadas.</td></tr>";
}
$tabla .= '</tbody></table>';

$contenidoPrincipal = "
<h1>Panel de Control: Gestión de Ofertas</h1>
<div class='admin-toolbar'>
    <a href='crear_oferta.php'><button class='btn-crear btn-lg'>+ Nueva Oferta</button></a>
</div>
{$tabla}";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
