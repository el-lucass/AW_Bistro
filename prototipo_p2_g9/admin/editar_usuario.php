<?php
// Subimos un nivel para encontrar la carpeta includes
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

// Verificación de seguridad básica
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
$conn = conectarBD(); //
$user = $conn->query("SELECT * FROM usuarios WHERE id = '$id'")->fetch_assoc();

if (!$user) {
    echo "Usuario no encontrado.";
    exit;
}

$tituloPagina = "Editando a: " . $user['nombre_usuario'];

$roles = ['cliente', 'camarero', 'cocinero', 'gerente'];
$opcionesRol = "";
foreach($roles as $r) {
    $sel = ($user['rol'] == $r) ? 'selected' : '';
    $opcionesRol .= "<option value='$r' $sel>$r</option>";
}

$contenidoPrincipal = <<<EOS
<h1>Editar Usuario: {$user['nombre_usuario']}</h1>
<form action="../usuarios/admin_editar.php" method="POST">
    <input type="hidden" name="id" value="{$user['id']}">
    <div><label>Nombre:</label> <input type="text" name="nombre" value="{$user['nombre']}"></div>
    <div><label>Email:</label> <input type="email" name="email" value="{$user['email']}"></div>
    <div><label>Rol:</label> <select name="rol">$opcionesRol</select></div>
    <br>
    <button type="submit">Actualizar Datos</button>
</form>
EOS;

// RAIZ_APP es absoluta, no necesita ../
require RAIZ_APP . '/vistas/plantillas/plantilla.php';