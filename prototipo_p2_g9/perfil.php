<?php
require_once 'includes/config.php';
require_once 'includes/usuarios.php';
require_once 'includes/clases/FormularioPerfil.php';

// Seguridad
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

$tituloPagina = 'Mi Perfil';

// 1. Obtener datos del usuario para pintar la foto GRANDE actual fuera del formulario
$id = $_SESSION['id'];
$user = buscaUsuario($id); // Usamos la función del modelo, no SQL directo

// 2. Lógica visual para saber qué ruta mostrar en la foto de la izquierda
$avatarActual = $user['avatar'];
$rutaImagen = '';

if (strpos($avatarActual, 'predefinidos/') !== false) {
    // Si es "predefinidos/jake.png", la ruta es img/avatares/predefinidos/jake.png
    $rutaImagen = "img/avatares/" . $avatarActual;
} elseif ($avatarActual == 'default.png') {
    $rutaImagen = "img/avatares/default.png";
} else {
    // Si es una foto subida "123123_foto.jpg"
    $rutaImagen = "img/avatares/usuarios/" . $avatarActual;
}

// 3. Gestionar el formulario de edición
$form = new FormularioPerfil();
$htmlFormulario = $form->gestiona();

// Mensaje de éxito si viene de la redirección
$msg = '';
if (isset($_GET['exito'])) {
    $msg = '<div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:5px;">¡Perfil actualizado con éxito!</div>';
}

// 4. Construcción de la página
$contenidoPrincipal = <<<EOS
<h1>Mi Perfil de Usuario</h1>
$msg

<div style="display:flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
    
    <div style="text-align: center; min-width: 200px;">
        <img src="$rutaImagen" alt="Avatar Actual" width="150" height="150" style="border-radius: 50%; border: 3px solid #d35400; object-fit: cover;">
        <p style="font-size: 1.2em; color: #d35400;"><strong>@{$user['nombre_usuario']}</strong></p>
        <p>BistroCoins: <strong>{$user['bistrocoins']}</strong> 🪙</p>
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

require 'includes/vistas/plantillas/plantilla.php';