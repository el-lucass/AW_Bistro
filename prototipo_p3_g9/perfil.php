<?php
require_once __DIR__.'/includes/config.php';

// Importamos las clases que vamos a usar
use es\ucm\fdi\aw\usuarios\FormularioPerfil;
use es\ucm\fdi\aw\usuarios\Usuario;

// Seguridad
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

$tituloPagina = 'Mi Perfil';

// 1. Obtener datos del usuario mediante la CLASE Usuario
$id = $_SESSION['id'];
$user = Usuario::buscaUsuario($id); 

// 2. Lógica visual: Usamos los GETTERS del objeto en lugar de los arrays
$avatarActual = $user->getAvatar();
$rutaImagen = '';

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
    $msg = '<div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:5px;">¡Perfil actualizado con éxito!</div>';
}

// Extraemos los datos del objeto a variables simples para usarlos fácilmente en el bloque HTML
$nombreUsuarioVista = htmlspecialchars($user->getNombreUsuario());

// NOTA SOBRE BISTROCOINS: Como no lo añadimos a las propiedades de la clase Usuario antes, 
// puedes ponerlo temporalmente a 0, o si lo has añadido a tu clase, usar $user->getBistrocoins().
$bistrocoinsVista = 0; 

// 4. Construcción de la página
$contenidoPrincipal = <<<EOS
<h1>Mi Perfil de Usuario</h1>
$msg

<div style="display:flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
    
    <div style="text-align: center; min-width: 200px;">
        <img src="$rutaImagen" alt="Avatar Actual" width="150" height="150" style="border-radius: 50%; border: 3px solid #d35400; object-fit: cover;">
        <p style="font-size: 1.2em; color: #d35400;"><strong>@$nombreUsuarioVista</strong></p>
        <p>BistroCoins: <strong>$bistrocoinsVista</strong> 🪙</p>
    </div>

    <div style="flex-grow: 1;">
        $htmlFormulario
    </div>

</div>

<div style="margin-top: 50px; padding: 20px; border: 1px solid #ffcccc; background: #fff5f5; border-radius: 8px;">
    <h3 style="color: #c0392b; margin-top: 0;">⚠️ Zona de Peligro</h3>
    <p>Si eliminas tu cuenta, no podrás recuperar tus BistroCoins ni tus pedidos. Esta acción es irreversible.</p>
    
    <form action="usuarios/borrar_cuenta.php" method="POST" onsubmit="return confirm('¿Estás COMPLETAMENTE seguro?');">
        <button type="submit" style="background: #e74c3c; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 5px;">
            🗑️ Eliminar mi cuenta permanentemente
        </button>
    </form>
</div>
EOS;

// Usamos la constante RAIZ_APP para evitar errores de rutas
require RAIZ_APP . '/vistas/plantillas/plantilla.php';