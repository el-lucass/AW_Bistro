<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\recompensas\Recompensa;

if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idRecompensa = intval($_POST['id']);
    Recompensa::borraRecompensa($idRecompensa);
}

header('Location: recompensas.php');
exit;
?>