<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['login'])) {
    $conn = conectarBD();
    $id = $_SESSION['id'];
    
    $res = $conn->query("SELECT avatar FROM usuarios WHERE id = $id");
    $userOld = $res->fetch_assoc();
    $avatarFinal = $userOld['avatar'];

    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];

    // 1. Si el usuario sube una foto propia (Prioridad máxima)
    if (!empty($_FILES['avatar_subida']['name'])) {
        $nombreArchivo = time() . "_" . $_FILES['avatar_subida']['name'];
        $rutaDestino = "../img/avatares/usuarios/" . $nombreArchivo;

        if (move_uploaded_file($_FILES['avatar_subida']['tmp_name'], $rutaDestino)) {
            $avatarFinal = $nombreArchivo;
            // Borrar anterior si era personalizada para no llenar el disco
            if (!str_contains($userOld['avatar'], 'predefinidos/') && $userOld['avatar'] !== 'default.png') {
                @unlink("../img/avatares/usuarios/" . $userOld['avatar']);
            }
        }
    } 
    // 2. Si elige un radio button (Predefinido o Default)
    elseif (isset($_POST['avatar_opcion'])) {
        $avatarFinal = $_POST['avatar_opcion'];
        // Borrar anterior si era personalizada
        if (!str_contains($userOld['avatar'], 'predefinidos/') && $userOld['avatar'] !== 'default.png') {
            @unlink("../img/avatares/usuarios/" . $userOld['avatar']);
        }
    }

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, email=?, avatar=? WHERE id=?");
    $stmt->bind_param("ssssi", $nombre, $apellidos, $email, $avatarFinal, $id);

    if ($stmt->execute()) {
        $_SESSION['nombre'] = $nombre;
        header('Location: ../perfil.php?status=success');
    } else {
        header('Location: ../perfil.php?status=error');
    }
    exit;
}