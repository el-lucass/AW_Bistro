<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

// Verificamos que sea gerente y que no se esté intentando borrar a sí mismo
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente' && $_POST['id'] != $_SESSION['id']) {
    $conn = conectarBD();
    $idABorrar = $_POST['id'];

    // 1. Buscamos el avatar del usuario ANTES de borrarlo de la base de datos
    $stmt_avatar = $conn->prepare("SELECT avatar FROM usuarios WHERE id = ?");
    $stmt_avatar->bind_param("i", $idABorrar);
    $stmt_avatar->execute();
    $resultado = $stmt_avatar->get_result();
    $usuario = $resultado->fetch_assoc();

    // 2. Procedemos a borrar el usuario de la base de datos
    $stmt_del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt_del->bind_param("i", $idABorrar);
    
    if ($stmt_del->execute()) {
        // 3. Si la base de datos se borró con éxito, limpiamos el archivo físico si es necesario
        // Solo borramos si no es un personaje (predefinidos) y no es el avatar por defecto
        if (!str_contains($usuario['avatar'], 'predefinidos/') && $usuario['avatar'] !== 'default.png') {
            $rutaFoto = "../img/avatares/usuarios/" . $usuario['avatar'];
            if (file_exists($rutaFoto)) {
                @unlink($rutaFoto);
            }
        }
    }
}

// Redirigimos de vuelta a la lista de usuarios
header('Location: ../admin/usuarios.php');
exit;