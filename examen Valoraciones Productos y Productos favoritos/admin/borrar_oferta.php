<?php
require_once '../includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\ofertas\Oferta;

// SEGURIDAD: Solo el gerente puede borrar ofertas
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

// RECUPERAR EL ID: Comprobamos que la petición viene por POST 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idOferta = intval($_POST['id']);

    // BORRADO
    $exito = Oferta::borraOferta($idOferta);

}

// volvemos a la tabla de ofertas
header('Location: ofertas.php');
exit;
?>