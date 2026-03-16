<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\usuarios\FormularioLogin;


$tituloPagina = 'Login';
$form = new FormularioLogin();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
<div class="caja-login">
    <h1>Acceso Usuarios</h1>
    $htmlFormulario
</div>
EOS;

require 'includes/vistas/plantillas/plantilla.php';