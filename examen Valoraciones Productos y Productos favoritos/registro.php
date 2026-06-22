<?php
require_once __DIR__.'/includes/config.php';
use es\ucm\fdi\aw\usuarios\FormularioRegistro;

$tituloPagina = 'Registro';

// Instanciamos y gestionamos
$form = new FormularioRegistro();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Registro de Cliente</h1>
$htmlFormulario
EOS;

require 'includes/vistas/plantillas/plantilla.php';