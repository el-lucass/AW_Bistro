<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\recompensas\FormularioCrearRecompensa;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Crear Nueva Recompensa';

$form = new FormularioCrearRecompensa();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = "
<a href='recompensas.php' class='nav-link mb-20'>← Volver a Recompensas</a>
<h1>Crear Nueva Recompensa</h1>
<div class='pagina-formulario'>
    {$htmlFormulario}
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>