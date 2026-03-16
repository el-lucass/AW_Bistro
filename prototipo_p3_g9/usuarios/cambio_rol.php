<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

// Doble seguridad: solo el gerente procesa esto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['rol']) && $_SESSION['rol'] === 'gerente') {
    $id_usuario = $_POST['id_usuario'];
    $nuevo_rol = $_POST['nuevo_rol'];

    // Evitar que el gerente se cambie a sí mismo (para no quedarse sin acceso)
    if ($id_usuario == $_SESSION['id']) {
        die("No puedes cambiar tu propio rol por seguridad.");
    }

    $conn = conectarBD();
    $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_rol, $id_usuario);

    if ($stmt->execute()) {
        header('Location: ../admin/usuarios.php?status=success');
    } else {
        echo "Error al actualizar el rol: " . $conn->error;
    }
} else {
    header('Location: ../index.php');
}