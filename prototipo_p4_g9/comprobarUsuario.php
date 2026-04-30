<?php

require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;


// Se usa desde FormularioRegistro, su .js (registro.js) para el ajax
if (isset($_GET['user'])) {
    $user = $_GET['user'];
    
    if (Usuario::buscaUsuarioPorNombre($user)) {
        echo "existe";
    } else {
        echo "disponible";
    }
}
?>