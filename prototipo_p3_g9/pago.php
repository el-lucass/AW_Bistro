<?php
require_once __DIR__.'/includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\pedidos\FormularioPago;

if (!isset($_SESSION['login']) || !Usuario::tieneRol('cliente')) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito']['productos'])) {
    header('Location: ' . resuelve('catalogo.php'));
    exit();
}

$tituloPagina = 'Pago - Bistro FDI';
$form = new FormularioPago($_SESSION['carrito']);

$contenidoPrincipal = "
<div class='pagina-centrada'>
    <div class='mb-20'>
        <a href='" . resuelve('carrito.php') . "' class='nav-link'>← Volver al carrito</a>
    </div>
    <h1 class='mt-0 mb-30'>Pago</h1>
    " . $form->gestiona() . "
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
