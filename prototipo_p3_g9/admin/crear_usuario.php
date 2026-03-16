<?php
require_once '../includes/config.php';
use es\ucm\fdi\aw\FormularioAdminCrear;

// Verificación de seguridad (Rol)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Alta de Empleado';

// Instanciamos y gestionamos
$form = new FormularioAdminCrear();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = <<<EOS
<h1>Añadir nuevo usuario (Panel Gerente)</h1>
$htmlFormulario
EOS;

require '../includes/vistas/plantillas/plantilla.php';