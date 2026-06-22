<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\pedidos\FormularioValoracion;

$id_producto = $_GET['id_producto'] ?? $_POST['id_producto'] ?? null;
if (!$id_producto) {
    header('Location: historial_pedidos.php');
    exit;
}

$tituloPagina = 'Valorar Producto';
$form = new FormularioValoracion($id_producto);
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
<div class="caja-login">
    <h1>Valorar Producto</h1>
    $htmlFormulario
</div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';