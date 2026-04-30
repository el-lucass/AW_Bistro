<?php

require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;

if (isset($_GET['user'])) {
    $user = $_GET['user'];
    
    // Te dice instant si el user ya existia en el "Registro"
    if (Usuario::buscaUsuarioPorNombre($user)) {
        echo "existe";
    } else {
        echo "disponible";
    }
}
?>