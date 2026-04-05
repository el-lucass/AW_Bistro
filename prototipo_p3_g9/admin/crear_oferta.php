<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\FormularioCrearOferta;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Crear Nueva Oferta';
$form         = new FormularioCrearOferta();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = "
<a href='ofertas.php' class='nav-link mb-20'>← Volver a Ofertas</a>
<h1>Crear Nueva Oferta</h1>
<div class='pagina-formulario'>
    {$htmlFormulario}
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
