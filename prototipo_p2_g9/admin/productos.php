<?php
// Subimos un nivel para encontrar los archivos necesarios
require_once '../includes/config.php';
require_once '../includes/productos.php';

// Verificación de seguridad: solo el gerente puede gestionar productos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Gestión de Productos - Bistro FDI';

// Obtenemos todos los productos (pasamos 'false' para que traiga también los retirados/borrados)
$productos = listaProductos(false);

// Gestión de mensajes de estado (éxito al crear, editar o borrar)
$mensaje = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>Producto retirado de la carta correctamente.</div>";
    } elseif ($_GET['status'] === 'restored') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>Producto añadido de vuelta a la carta.</div>";
    } elseif ($_GET['status'] === 'updated') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>Producto actualizado correctamente.</div>";
    } elseif ($_GET['status'] === 'created') {
        $mensaje = "<div style='background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px;'>¡Producto creado y añadido a la carta correctamente!</div>";
    }
}

// Construimos la tabla
$tabla = '<table border="1" style="width:100%; text-align:left; border-collapse: collapse;">
    <tr style="background-color: #f2f2f2;">
        <th style="padding: 10px;">ID</th>
        <th style="padding: 10px;">Foto</th>
        <th style="padding: 10px;">Nombre</th>
        <th style="padding: 10px;">Categoría</th>
        <th style="padding: 10px;">Precio Final</th>
        <th style="padding: 10px;">Stock</th>
        <th style="padding: 10px;">Estado (Carta)</th>
        <th style="padding: 10px;">Acciones</th>
    </tr>';

if (!empty($productos)) {
    foreach ($productos as $row) {
        $id = $row['id'];
        
        // 1. Mostrar miniatura de la imagen principal o una por defecto
        $rutaImagen = $row['imagen_principal'] ? "../img/productos/" . $row['imagen_principal'] : "../img/productos/default_food.png";
        $imgTag = "<img src='{$rutaImagen}' width='50' height='50' style='object-fit: cover; border-radius: 5px;'>";
        
        // 2. Cálculo del precio final (Precio Base + IVA)
        $precioFinal = $row['precio_base'] * (1 + ($row['iva'] / 100));
        $precioFormateado = number_format($precioFinal, 2, ',', '.') . " €";
        
        // 3. Etiquetas visuales para Stock (Disponible) y Carta (Ofertado)
        $stockBadge = $row['disponible'] 
            ? "<span style='color: green; font-weight: bold;'>Disponible</span>" 
            : "<span style='color: red; font-weight: bold;'>Agotado</span>";
            
        $estadoBadge = $row['ofertado'] 
            ? "<span style='color: #2980b9; font-weight: bold;'>En Carta</span>" 
            : "<span style='color: gray; font-style: italic;'>Retirado</span>";

        $tabla .= "<tr id='fila-producto-{$id}'>
            <td style='padding: 10px;'>{$id}</td>
            <td style='padding: 10px; text-align:center;'>{$imgTag}</td>
            <td style='padding: 10px;'><strong>{$row['nombre']}</strong></td>
            <td style='padding: 10px;'>{$row['nombre_categoria']}</td>
            <td style='padding: 10px;'>
                {$precioFormateado}<br>
                <small style='color: #666;'>(Base: {$row['precio_base']}€ + {$row['iva']}% IVA)</small>
            </td>
            <td style='padding: 10px;'>{$stockBadge}</td>
            <td style='padding: 10px;'>{$estadoBadge}</td>
            <td style='padding: 10px;'>
                <a href='editar_producto.php?id=$id'><button style='background-color:#f39c12; color:white; border:none; padding:5px; cursor:pointer; margin-bottom: 5px;'>Editar</button></a>";
        
        // Botón dinámico: si está ofertado mostramos "Retirar", si no, "Restaurar"
        if ($row['ofertado']) {
            $tabla .= " <form action='../productos/admin_borrar_producto.php' method='POST' style='display:inline;'>
                            <input type='hidden' name='id' value='$id'>
                            <input type='hidden' name='accion' value='retirar'>
                            <button type='submit' style='background-color:#c0392b; color:white; border:none; padding:5px; cursor:pointer;'>Retirar</button>
                        </form>";
        } else {
            $tabla .= " <form action='../productos/admin_borrar_producto.php' method='POST' style='display:inline;'>
                            <input type='hidden' name='id' value='$id'>
                            <input type='hidden' name='accion' value='restaurar'>
                            <button type='submit' style='background-color:#27ae60; color:white; border:none; padding:5px; cursor:pointer;'>Restaurar</button>
                        </form>";
        }
        
        $tabla .= "</td></tr>";
    }
} else {
    $tabla .= "<tr><td colspan='8' style='padding: 10px; text-align:center;'>No hay productos registrados en la base de datos.</td></tr>";
}
$tabla .= '</table>';

$contenidoPrincipal = <<<EOS
    <h1>Panel de Control: Gestión de Productos</h1>
    
    <div style='margin-bottom: 20px; display: flex; gap: 15px;'>
        <a href='crear_producto.php'>
            <button style='background-color:#2ecc71; color:white; padding:10px 15px; cursor:pointer; border:none; border-radius:5px; font-weight:bold;'>
                + Añadir Nuevo Producto
            </button>
        </a>
        <a href='categorias.php'>
            <button style='background-color:#3498db; color:white; padding:10px 15px; cursor:pointer; border:none; border-radius:5px; font-weight:bold;'>
                📁 Gestionar Categorías
            </button>
        </a>
    </div>

    $tabla
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>