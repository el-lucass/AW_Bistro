<?php
require_once '../includes/config.php';
require_once '../includes/usuarios.php'; // Incluye el modelo con las funciones buscaUsuario y actualizaUsuario

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['login'])) {
    $id = $_SESSION['id'];
    
    // 1. Obtenemos los datos actuales mediante el Modelo para saber qué avatar tiene
    $userOld = buscaUsuario($id);
    if (!$userOld) {
        header('Location: ../perfil.php?status=error');
        exit;
    }

    $avatarFinal = $userOld['avatar'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $rol = $_SESSION['rol']; // El usuario no cambia su propio rol, mantenemos el de la sesión

    // --- Lógica de Gestión de Archivos (Avatar) ---

    // Prioridad 1: Subida de foto nueva
    if (!empty($_FILES['avatar_subida']['name'])) {
        $nombreArchivo = time() . "_" . $_FILES['avatar_subida']['name'];
        $rutaDestino = "../img/avatares/usuarios/" . $nombreArchivo;

        if (move_uploaded_file($_FILES['avatar_subida']['tmp_name'], $rutaDestino)) {
            $avatarFinal = $nombreArchivo;
            // Borramos la imagen antigua si era personalizada
            if (!str_contains($userOld['avatar'], 'predefinidos/') && $userOld['avatar'] !== 'default.png') {
                @unlink("../img/avatares/usuarios/" . $userOld['avatar']);
            }
        }
    } 
    // Prioridad 2: Selección de radio button (Predefinido o Reset)
    elseif (isset($_POST['avatar_opcion'])) {
        $avatarFinal = $_POST['avatar_opcion'];
        // Borramos la antigua si era personalizada
        if (!str_contains($userOld['avatar'], 'predefinidos/') && $userOld['avatar'] !== 'default.png') {
            @unlink("../img/avatares/usuarios/" . $userOld['avatar']);
        }
    }

    // 2. Ejecutamos la actualización mediante la función del Modelo
    // Pasamos el ID, datos personales, el avatar decidido y el rol actual
    if (actualizaUsuario($id, $nombre, $apellidos, $email, $avatarFinal, $rol)) {
        $_SESSION['nombre'] = $nombre; // Actualizamos el saludo en la cabecera
        header('Location: ../perfil.php?status=success');
    } else {
        header('Location: ../perfil.php?status=error');
    }
    exit;
}