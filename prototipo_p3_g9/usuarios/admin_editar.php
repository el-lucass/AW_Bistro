<?php
require_once '../includes/config.php';

// Verificación de seguridad: solo el gerente puede procesar esto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente') {
    
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];

    if ($id && $nombre && $email && $rol) {
        $conn = conectarBD();
        
        // Preparamos la consulta para actualizar nombre, email y rol
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $email, $rol, $id);

        if ($stmt->execute()) {
            // Si todo va bien, volvemos a la lista de usuarios
            header('Location: ../admin/usuarios.php?status=updated');
            exit;
        } else {
            echo "Error al actualizar el usuario: " . $conn->error;
        }
    } else {
        echo "Faltan datos obligatorios en el formulario.";
    }
} else {
    // Si alguien intenta acceder directamente al archivo sin ser gerente
    header('Location: ../index.php');
    exit;
}