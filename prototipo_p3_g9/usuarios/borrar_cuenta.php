<?php
// 1. Cargamos el config (que ya trae el Autoloader)
require_once '../includes/config.php';

// 2. Usamos el namespace de la clase Usuario
use es\ucm\fdi\aw\Usuario;

// 3. Verificamos que el usuario esté logueado
if (isset($_SESSION['login'])) {
    $id = $_SESSION['id'];
    
    // 4. Llamamos a los métodos estáticos de la CLASE
    $usuario = Usuario::buscaUsuario($id);

    if (Usuario::borraUsuario($id)) {
        // Limpieza de avatar personalizado si existe
        if ($usuario) {
            // Usamos el GETTER en lugar del array
            $avatar = $usuario->getAvatar();
            
            if (!str_contains($avatar, 'predefinidos/') && $avatar !== 'default.png') {
                // Borramos el archivo físico de la foto
                @unlink("../img/avatares/usuarios/" . $avatar);
            }
        }
        
        // Destruimos la sesión porque el usuario ya no existe
        session_destroy();
        header('Location: ' . RUTA_APP . '/index.php');
        exit;
    }
}

// Redirección de seguridad por si alguien entra aquí sin estar logueado o falla el borrado
header('Location: ' . RUTA_APP . '/index.php');
exit;