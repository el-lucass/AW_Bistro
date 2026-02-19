<?php
require_once '../includes/config.php';
require_once '../includes/usuarios.php';

// Protección de acceso
if (!tieneRol('gerente')) {
    header('Location: ../index.php');
    exit;
}

$tituloPagina = "Añadir Empleado/Cliente";

// Gestión de mensajes de error
$mensaje = "";
if (isset($_GET['error']) && $_GET['error'] === 'usuario_existe') {
    $nombre = htmlspecialchars($_GET['intento']);
    $mensaje = "<div style='background: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px;'>
                    <strong>Error:</strong> El nombre de usuario '$nombre' ya existe en la base de datos.
                </div>";
}

$contenidoPrincipal = <<<EOS
    <h1>Añadir nuevo usuario</h1>
    {$mensaje}
    <form action="../usuarios/admin_crear.php" method="POST">
        <fieldset>
            <legend>Datos de la cuenta</legend>
            <label>Usuario:</label> <input type="text" name="nombre_usuario" required>
            <label>Contraseña:</label> <input type="password" name="password" required>
            
            <label>Rol en el restaurante:</label>
            <select name="rol">
                <option value="cliente">Cliente</option>
                <option value="camarero">Camarero</option>
                <option value="cocinero">Cocinero</option>
                <option value="gerente">Gerente</option>
            </select>
        </fieldset>
        
        <fieldset style="margin-top:10px;">
            <legend>Datos personales</legend>
            <label>Nombre:</label> <input type="text" name="nombre" required>
            <label>Apellidos:</label> <input type="text" name="apellidos" required>
            <label>Email:</label> <input type="email" name="email" required>
        </fieldset>

        <button type="submit" style="margin-top:15px; padding:10px; background:#2980b9; color:white; border:none;">
            Crear Usuario
        </button>
    </form>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';