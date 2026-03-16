<?php
require_once __DIR__.'/includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\pedidos\FormularioPago;

// Seguridad: Solo clientes logueados
if (!isset($_SESSION['login']) || !Usuario::tieneRol('cliente')) {
    header('Location: login.php');
    exit();
}

// Si el carrito está vacío, volvemos al catálogo
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    header('Location: catalogo.php');
    exit();
}

$tituloPagina = 'Pago - Bistro FDI';

$form = new FormularioPago($_SESSION['carrito']);

$contenidoPrincipal = "
<div style='padding: 20px; max-width: 800px; margin: 0 auto;'>

    <div style='margin-bottom: 20px; display: flex; justify-content: flex-start;'>
        <a href='carrito.php' style='text-decoration: none;'>
            <button type='button' style='background-color: white; color: #333; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; cursor: pointer;'>
                ← Volver al carrito
            </button>
        </a>
    </div>

    <h1 style='margin-top: 0; margin-bottom: 30px;'>Pago</h1>

    " . $form->gestiona() . "

</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
