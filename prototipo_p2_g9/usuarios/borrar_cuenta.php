<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['login'])) {
    $conn = conectarBD();
    $id = $_SESSION['id'];

    // 1. Consultamos el avatar para borrar el archivo físico
    $res = $conn->query("SELECT avatar FROM usuarios WHERE id = $id");
    $user = $res->fetch_assoc();

    // 2. Borramos el usuario de la BD
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // 3. Si tenía foto personalizada, la borramos del servidor
        if (!str_contains($user['avatar'], 'predefinidos/') && $user['avatar'] !== 'default.png') {
            @unlink("../img/avatares/usuarios/" . $user['avatar']);
        }

        // 4. Cerramos sesión y mandamos al inicio
        session_unset();
        session_destroy();
        header('Location: ' . RUTA_APP . '/index.php?msg=cuenta_eliminada');
        exit;
    } else {
        echo "Error al eliminar la cuenta.";
    }
}