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

$contenidoPrincipal = <<<EOS
    <a href="ofertas.php" style="text-decoration:none; color:#2980b9;">&larr; Volver a Ofertas</a>
    <h1>Crear Nueva Oferta (Panel Gerente)</h1>
    
    <div style="background: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px; max-width: 800px;">
        $htmlFormulario
    </div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>