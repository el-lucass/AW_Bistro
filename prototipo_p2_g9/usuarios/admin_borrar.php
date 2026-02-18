<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';
if ($_SESSION['rol'] === 'gerente' && $_POST['id'] != $_SESSION['id']) {
    $conn = conectarBD();
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
}
header('Location: ../admin/usuarios.php');