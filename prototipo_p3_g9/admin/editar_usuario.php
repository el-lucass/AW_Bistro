<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\usuarios\FormularioAdminEditar;

// 1. SEGURIDAD: Solo gerentes pueden entrar
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// 2. RECUPERAR EL ID (Por GET o por POST si el form ya se envió)
$idUsuario = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$idUsuario) {
    header('Location: usuarios.php');
    exit;
}

$tituloPagina = 'Editar Usuario';

// 3. INSTANCIAR EL FORMULARIO pasándole el ID
$form = new FormularioAdminEditar($idUsuario);
$htmlFormulario = $form->gestiona();

// 4. PINTAR LA VISTA
$contenidoPrincipal = <<<EOS
<div class="caja-admin">
    <h1>Editar datos del usuario</h1>
    <a href="usuarios.php" style="display:inline-block; margin-bottom: 15px;">⬅️ Volver a la lista</a>
    
    $htmlFormulario
</div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';