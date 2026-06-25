<?php
require_once __DIR__.'/includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\incidencias\Incidencia;
use es\ucm\fdi\aw\incidencias\FormularioPonerIncidencia;


$id_pedido = $_GET['id_pedido'] ?? $_POST['id_pedido'] ?? null;
if (!$id_pedido) {
    header('Location: historial_pedidos.php');
    exit;
}

// 3. INSTANCIAR EL FORMULARIO pasándole el ID
$form = new FormularioPonerIncidencia($id_pedido);
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = "
<div class='caja-admin'>
    <h1>Poner una incidencia</h1>
    <a href='historial_pedidos.php'>⬅️ Volver al historial de pedidos</a>
    {$htmlFormulario}
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
