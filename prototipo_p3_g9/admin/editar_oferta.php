<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\FormularioEditarOferta;

// SEGURIDAD: Solo gerentes pueden entrar
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// RECUPERAR EL ID de la oferta (Por GET o por POST si el form ya se envió)
$idOferta = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$idOferta) {
    header('Location: ofertas.php');
    exit;
}

$tituloPagina = 'Editar Oferta - Bistro FDI';

// INSTANCIAR EL FORMULARIO pasándole el ID de la oferta
$form = new FormularioEditarOferta($idOferta);
$htmlFormulario = $form->gestiona();

// La vista como tal
$contenidoPrincipal = <<<EOS
<div style="max-width: 800px; margin: 0 auto; background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
    <a href="ofertas.php" style="text-decoration:none; color:#2980b9; display:inline-block; margin-bottom: 15px;">&larr; Volver a la lista de ofertas</a>
    
    <h1>Editar Oferta Promocional</h1>
    <p style="color: #555; margin-bottom: 20px;">
        Modifica los datos principales o los productos asociados a la oferta. Los cambios se aplicarán a los nuevos pedidos.
    </p>
    
    $htmlFormulario
</div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>