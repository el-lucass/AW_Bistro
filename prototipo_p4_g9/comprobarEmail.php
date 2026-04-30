<?php
require_once __DIR__.'/includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario; 

// Se usa desde FormularioRegistro.php desde su .js (registro.js) para ajax
if (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    
    $usuario = Usuario::buscaUsuarioPorEmail($email);
    
    if ($usuario) {
        echo "existe";
    } else {
        echo "disponible";
    }
} else {
    echo "error";
}
?>