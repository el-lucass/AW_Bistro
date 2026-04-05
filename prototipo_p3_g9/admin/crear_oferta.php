<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\FormularioCrearOferta;

// Verificación de seguridad (Rol)
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = 'Crear Nueva Oferta';

// Instanciamos el formulario que acabamos de crear
$form = new FormularioCrearOferta();
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = "
<a href='ofertas.php' class='nav-link mb-20' style='display:inline-block;'>← Volver a Ofertas</a>
<h1>Crear Nueva Oferta</h1>
<div class='pagina-formulario'>
    {$htmlFormulario}
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
