<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\FormularioEditarOferta;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$idOferta = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$idOferta) {
    header('Location: ofertas.php');
    exit;
}

$tituloPagina   = 'Editar Oferta - Bistro FDI';
$form           = new FormularioEditarOferta($idOferta);
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = "
<div class='pagina-formulario'>
    <a href='ofertas.php' class='nav-link mb-15'>← Volver a la lista de ofertas</a>
    <h1>Editar Oferta Promocional</h1>
    <p class='texto-gris mb-20'>Modifica los datos principales o los productos asociados a la oferta.</p>
    {$htmlFormulario}
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>
