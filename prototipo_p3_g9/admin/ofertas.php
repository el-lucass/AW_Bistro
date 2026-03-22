<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\Oferta;

// Verificación de seguridad: Solo el gerente puede gestionar ofertas
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Ofertas - Bistro FDI';

// Obtenemos todas las ofertas (actuales y pasadas) usando la clase Oferta
$listaOfertas = Oferta::listaTodasLasOfertas();

// AÑADIDO: Columnas separadas para Nombre y Descripción
$tabla = '<table border="1" style="width:100%; text-align:left; border-collapse: collapse;">
    <tr style="background-color: #f2f2f2;">
        <th style="padding: 10px;">ID</th>
        <th style="padding: 10px;">Nombre</th>
        <th style="padding: 10px;">Descripción</th>
        <th style="padding: 10px;">Productos Requeridos</th>
        <th style="padding: 10px;">Descuento</th>
        <th style="padding: 10px;">Disponibilidad</th>
        <th style="padding: 10px;">Estado</th>
        <th style="padding: 10px;">Acciones</th>
    </tr>';

if (!empty($listaOfertas)) {
    $fechaActual = new \DateTime(); // Para comprobar si la oferta está activa hoy

    foreach ($listaOfertas as $oferta) {
        $id = $oferta->getId();
        
        // Formateamos la lista de productos requeridos para mostrarlos bien
        $productosArray = $oferta->getProductos();
        $listaProductosHtml = '';
        if (!empty($productosArray)) {
            foreach ($productosArray as $prod) {
                $listaProductosHtml .= "• " . htmlspecialchars($prod['cantidad_requerida'] . "x " . $prod['nombre']) . "<br>";
            }
        } else {
            $listaProductosHtml = "<em>Sin productos</em>";
        }

        // Formateamos las fechas para que sean legibles
        $fechaInicio = new \DateTime($oferta->getFechaInicio());
        $fechaFin = new \DateTime($oferta->getFechaFin());
        $strFechas = $fechaInicio->format('d/m/Y') . ' - ' . $fechaFin->format('d/m/Y');

        // Comprobamos si está activa o caducada
        if ($fechaActual >= $fechaInicio && $fechaActual <= $fechaFin) {
            $estado = "<span style='color: #27ae60; font-weight: bold;'>Activa</span>";
        } else {
            $estado = "<span style='color: #c0392b; font-weight: bold;'>Inactiva / Pasada</span>";
        }

        // AÑADIDO: Dos <td> separados para el nombre y la descripción
        $tabla .= "<tr>
            <td style='padding: 10px;'>{$id}</td>
            <td style='padding: 10px;'><strong>" . htmlspecialchars($oferta->getNombre()) . "</strong></td>
            <td style='padding: 10px; font-size: 0.9em; color: #555; max-width: 250px;'>" . htmlspecialchars($oferta->getDescripcion()) . "</td>
            <td style='padding: 10px;'>{$listaProductosHtml}</td>
            <td style='padding: 10px; font-weight: bold; color: #2980b9;'>" . $oferta->getPorcentajeDescuento() . "%</td>
            <td style='padding: 10px; font-size: 0.9em;'>{$strFechas}</td>
            <td style='padding: 10px;'>{$estado}</td>
            <td style='padding: 10px;'>
                <a href='editar_oferta.php?id=$id'><button style='background-color:#f39c12; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:3px; margin-bottom: 5px;'>Editar</button></a>
                
                <form action='borrar_oferta.php' method='POST' style='display:inline;' onsubmit='return confirm(\"¿Estás seguro de borrar esta oferta? Se perderá permanentemente.\")'>
                    <input type='hidden' name='id' value='$id'>
                    <button type='submit' style='background-color:#c0392b; color:white; border:none; padding:5px 10px; cursor:pointer; border-radius:3px;'>Borrar</button>
                </form>
            </td>
        </tr>";
    }
} else {
    // AÑADIDO: colspan='8' porque ahora tenemos 8 columnas
    $tabla .= "<tr><td colspan='8' style='padding: 10px; text-align:center;'>No hay ofertas registradas actualmente.</td></tr>";
}
$tabla .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Panel de Control: Gestión de Ofertas</h1>
    <p>Lista de todas las ofertas promocionales. Las ofertas inactivas no serán visibles para los clientes.</p>
    
    <div style="margin-bottom: 20px;">
        <a href='crear_oferta.php'>
            <button style='background-color:#2ecc71; color:white; padding:10px; cursor:pointer; border:none; border-radius:5px; font-weight: bold;'>
                + Crear Nueva Oferta
            </button>
        </a>
    </div>

    $tabla
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>