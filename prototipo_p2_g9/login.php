<?php
require_once 'includes/config.php';

$tituloPagina = 'Login - Bistro FDI';

// Definimos el formulario como contenido principal
// El action apunta a procesarLogin.php que es quien tiene la lógica de BD
$contenidoPrincipal = <<<EOS
    <h1>Iniciar Sesión</h1>
    <form action="procesarLogin.php" method="POST">
        <fieldset>
            <legend>Datos de acceso</legend>
            <div>
                <label>Nombre de usuario:</label>
                <input type="text" name="nombre_usuario" required>
            </div>
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            <br>
            <button type="submit">Entrar</button>
        </fieldset>
    </form>
    <p>¿Aún no tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
EOS;

// Usamos la ruta corregida para evitar el error de "includes/includes"
require RAIZ_APP . '/vistas/plantillas/plantilla.php';