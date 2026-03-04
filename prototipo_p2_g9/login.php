<?php
require_once 'includes/config.php';
require_once 'includes/clases/FormularioLogin.php';

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