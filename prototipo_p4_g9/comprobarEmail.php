<?php
require_once __DIR__ . '/includes/config.php';
use es\ucm\fdi\aw\usuarios\Usuario;

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    
    // Suponiendo que has añadido la función buscaUsuarioPorEmail en tu clase Usuario
    if (Usuario::buscaUsuarioPorEmail($email)) {
        echo "existe";
    } else {
        echo "disponible";
    }
}
?>