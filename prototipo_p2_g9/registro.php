<?php
require_once 'includes/config.php';

$tituloPagina = 'Registro de Usuario';

// --- Lógica para mostrar mensajes de error ---
$mensajeError = "";
if (isset($_GET['error'])) {
    // Limpiamos el nombre que viene por la URL para evitar ataques XSS
    $nombreFallido = htmlspecialchars($_GET['intento'] ?? 'usuario');

    if ($_GET['error'] === 'usuario_existe') {
        $mensajeError = "
        <div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;'>
            <strong>¡Error!</strong> El nombre de usuario <strong>'$nombreFallido'</strong> ya está registrado. Por favor, elige otro.
        </div>";
    } elseif ($_GET['error'] === 'campos_vacios') {
        $mensajeError = "
        <div style='background: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 20px;'>
            Por favor, rellena todos los campos marcados como obligatorios.
        </div>";
    }
}

$contenidoPrincipal = <<<EOS
<div style="max-width: 500px; margin: auto;">
    <h1>Crear nueva cuenta</h1>
    <p>Únete a la comunidad de AW Bistro y empieza a ganar BistroCoins.</p>
    
    {$mensajeError}

    <form action="usuarios/registro.php" method="POST">
        <fieldset>
            <legend>Datos de acceso</legend>
            <div>
                <label>Nombre de Usuario*:</label>
                <input type="text" name="nombre_usuario" required placeholder="Ej: chewbacca_88">
            </div>
            <div>
                <label>Contraseña*:</label>
                <input type="password" name="password" required>
            </div>
        </fieldset>

        <fieldset style="margin-top: 20px;">
            <legend>Datos personales</legend>
            <div>
                <label>Nombre*:</label>
                <input type="text" name="nombre" required>
            </div>
            <div>
                <label>Apellidos:</label>
                <input type="text" name="apellidos">
            </div>
            <div>
                <label>Email*:</label>
                <input type="email" name="email" required>
            </div>
        </fieldset>

        <div style="margin-top: 20px; text-align: center;">
            <button type="submit" style="padding: 10px 30px; background-color: #d35400; color: white; border: none; cursor: pointer;">
                Registrarse
            </button>
        </div>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">
        ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
    </p>
</div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';