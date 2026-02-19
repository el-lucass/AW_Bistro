<?php
// 1. Cargamos configuración (para sesion_start y RUTA_APP) y el modelo
require_once '../includes/config.php';
require_once '../includes/usuarios.php';

// 2. Verificamos permisos y que no se borre a sí mismo
if (tieneRol('gerente') && $_POST['id'] != $_SESSION['id']) {
    $id = $_POST['id'];

    // 3. Usamos el modelo para buscar el usuario ANTES de borrarlo (para saber su avatar)
    $usuario = buscaUsuario($id);

    if ($usuario) {
        // 4. Usamos el modelo para borrar el registro en la base de datos
        if (borraUsuario($id)) {
            // 5. Si se borró de la BD, limpiamos el archivo físico si es personalizado
            if (!str_contains($usuario['avatar'], 'predefinidos/') && $usuario['avatar'] !== 'default.png') {
                $rutaFoto = "../img/avatares/usuarios/" . $usuario['avatar'];
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