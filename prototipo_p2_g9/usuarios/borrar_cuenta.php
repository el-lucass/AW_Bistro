<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['login'])) {
    $conn = conectarBD();
    $id = $_SESSION['id'];

    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        session_destroy();
        header('Location: ../index.php?msg=cuenta_borrada');
        exit;
    } else {
        echo "No se pudo borrar la cuenta.";
    }
}