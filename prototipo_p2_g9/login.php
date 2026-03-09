<?php
require_once 'includes/config.php';
use es\ucm\fdi\aw\FormularioLogin;


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