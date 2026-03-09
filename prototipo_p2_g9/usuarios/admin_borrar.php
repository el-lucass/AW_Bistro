<?php
// 1. Cargamos configuración (que ya incluye el Autoloader)
require_once '../includes/config.php';

// ¡NUEVO! Importamos la clase Usuario
use es\ucm\fdi\aw\Usuario;

// 2. Verificamos permisos (Usuario::tieneRol) y que no se borre a sí mismo
if (Usuario::tieneRol('gerente') && isset($_POST['id']) && $_POST['id'] != $_SESSION['id']) {
    $id = $_POST['id'];

    // 3. Buscamos el usuario ANTES de borrarlo (ahora devuelve un OBJETO)
    $usuario = Usuario::buscaUsuario($id);

    if ($usuario) {
        // 4. Borramos el registro en la base de datos
        if (Usuario::borraUsuario($id)) {
            
            // 5. Extraemos el avatar usando el GETTER en lugar del array
            $avatar = $usuario->getAvatar();
            
            // Limpiamos el archivo físico si es personalizado
            if (!str_contains($avatar, 'predefinidos/') && $avatar !== 'default.png') {
                $rutaFoto = "../img/avatares/usuarios/" . $avatar;
                if (file_exists($rutaFoto)) {
                    @unlink($rutaFoto);
                }
            }
        }
    }
}

// 6. REDIRECCIÓN: Fundamental para no quedarte en una página en blanco
header('Location: ' . RUTA_APP . '/admin/usuarios.php');
exit;