<?php
require_once __DIR__.'/includes/config.php';

// Importamos las clases que vamos a usar
use es\ucm\fdi\aw\usuarios\FormularioPerfil;
use es\ucm\fdi\aw\usuarios\Usuario;

// Seguridad
if (!isset($_SESSION['login'])) {
    header('Location: ' . resuelve('/login.php'));
    exit;
}

$tituloPagina = 'Mi Perfil';

// 1. Obtener datos del usuario mediante la CLASE Usuario
$id = $_SESSION['id'];
$user = Usuario::buscaUsuario($id);

// 2. Lógica visual: Usamos los GETTERS del objeto en lugar de los arrays
$avatarActual = $user->getAvatar();
if (strpos($avatarActual, 'predefinidos/') !== false) {
    $rutaImagen = resuelve("/img/avatares/" . $avatarActual);
} elseif ($avatarActual == 'default.png') {
    $rutaImagen = resuelve("/img/avatares/default.png");
} else {
    $rutaImagen = resuelve("/img/avatares/usuarios/" . $avatarActual);
}

// 3. Gestionar el formulario de edición
$form = new FormularioPerfil();
$htmlFormulario = $form->gestiona();

$msg = '';
if (isset($_GET['exito'])) {
    $msg = '<div class="alerta-exito">¡Perfil actualizado con éxito!</div>';
}

// Extraemos los datos del objeto a variables simples para usarlos fácilmente en el bloque HTML
$nombreUsuarioVista = htmlspecialchars($user->getNombreUsuario());

// NOTA SOBRE BISTROCOINS: Como no lo añadimos a las propiedades de la clase Usuario antes, 
// puedes ponerlo temporalmente a 0, o si lo has añadido a tu clase, usar $user->getBistrocoins().
$bistrocoinsVista = 0; 
$urlBorrarCuenta = resuelve('/usuarios/borrar_cuenta.php');

// 4. Construcción de la página
$contenidoPrincipal = <<<EOS
<h1>Mi Perfil de Usuario</h1>
$msg

<div class="perfil-layout">
    <div class="perfil-avatar-sec">
        <img src="$rutaImagen" alt="Avatar Actual" width="150" height="150" class="perfil-avatar-img">
        <p class="perfil-username"><strong>@$nombreUsuarioVista</strong></p>
        <p>BistroCoins: <strong>$bistrocoinsVista</strong> 🪙</p>
    </div>
    <div class="perfil-datos-sec">
        $htmlFormulario
    </div>
</div>

<div class="zona-peligro">
    <h3 class="zona-peligro-titulo">⚠️ Zona de Peligro</h3>
    <p>Si eliminas tu cuenta, no podrás recuperar tus BistroCoins ni tus pedidos. Esta acción es irreversible.</p>
    <form action="$urlBorrarCuenta" method="POST" onsubmit="return confirm('¿Estás COMPLETAMENTE seguro?');">
        <button type="submit" class="btn-peligro btn-lg">🗑️ Eliminar mi cuenta permanentemente</button>
    </form>
</div>
EOS;

// Usamos la constante RAIZ_APP para evitar errores de rutas
require RAIZ_APP . '/vistas/plantillas/plantilla.php';