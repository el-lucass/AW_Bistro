<?php
require_once 'includes/config.php';
require_once 'includes/clases/FormularioRegistro.php';

$tituloPagina = 'Registro';

// Instanciamos y gestionamos
$form = new FormularioRegistro();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Registro de Cliente</h1>
$htmlFormulario
EOS;

require 'includes/vistas/plantillas/plantilla.php';