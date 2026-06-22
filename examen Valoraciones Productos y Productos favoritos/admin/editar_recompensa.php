<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\recompensas\FormularioEditarRecompensa;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$idRecompensa = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$idRecompensa) {
    header('Location: recompensas.php');
    exit;
}

$tituloPagina = 'Editar Recompensa - Bistro FDI';

$form = new FormularioEditarRecompensa($idRecompensa);
$htmlFormulario = $form->gestiona();

$contenidoPrincipal = "
<div class='pagina-formulario'>
    <a href='recompensas.php' class='nav-link mb-15'>← Volver a la lista de recompensas</a>
    <h1>Editar Recompensa</h1>
    <p class='texto-gris mb-20'>Modifica el producto asociado, el coste en BistroCoins o su estado.</p>
    {$htmlFormulario}
</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>