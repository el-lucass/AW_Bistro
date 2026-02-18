<?php
require_once 'includes/config.php';
require_once 'includes/mysql/bd.php';

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

$tituloPagina = 'Mi Perfil - Bistro FDI';

// Obtenemos los datos actuales del usuario
$conn = conectarBD();
$idUsuario = $_SESSION['id'];
$query = "SELECT * FROM usuarios WHERE id = '$idUsuario'";
$result = $conn->query($query);
$user = $result->fetch_assoc();

$contenidoPrincipal = <<<EOS
<h1>Mi Perfil</h1>
<form action="usuarios/perfil.php" method="POST">
    <fieldset>
        <legend>Actualizar mis datos</legend>
        <div><label>Nombre:</label> <input type="text" name="nombre" value="{$user['nombre']}" required></div>
        <div><label>Apellidos:</label> <input type="text" name="apellidos" value="{$user['apellidos']}" required></div>
        <div><label>Email:</label> <input type="email" name="email" value="{$user['email']}" required></div>
        <div><label>Nueva contraseña (dejar en blanco para no cambiar):</label> 
             <input type="password" name="password"></div>
        <br>
        <button type="submit">Guardar cambios</button>
    </fieldset>
</form>

<div style="margin-top: 50px; border: 1px solid red; padding: 20px;">
    <h3>Zona de Peligro</h3>
    <p>Si borras tu cuenta, perderás tus BistroCoins y no podrás recuperar tus pedidos.</p>
    <form action="usuarios/borrar_cuenta.php" method="POST" onsubmit="return confirm('¿Estás COMPLETAMENTE seguro? Esta acción no se puede deshacer.');">
        <button type="submit" style="background-color: #ff4d4d; color: white;">Borrar mi cuenta definitivamente</button>
    </form>
</div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';