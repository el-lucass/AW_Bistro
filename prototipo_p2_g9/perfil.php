<?php
require_once 'includes/config.php';
require_once 'includes/mysql/bd.php';

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

$conn = conectarBD();
$id = $_SESSION['id'];
$user = $conn->query("SELECT * FROM usuarios WHERE id = '$id'")->fetch_assoc();

$tituloPagina = "Mi Perfil";

// Lógica de rutas para la imagen
$avatarActual = $user['avatar'];
if (strpos($avatarActual, 'predefinidos/') !== false) {
    $rutaImagen = RUTA_IMGS . "avatares/" . $avatarActual;
} elseif ($avatarActual == 'default.png') {
    $rutaImagen = RUTA_IMGS . "avatares/default.png";
} else {
    $rutaImagen = RUTA_IMGS . "avatares/usuarios/" . $avatarActual;
}

$contenidoPrincipal = <<<EOS
<h1>Mi Perfil</h1>
<div style="display:flex; gap: 30px; align-items: flex-start;">
    <div style="text-align: center;">
        <img src="{$rutaImagen}" alt="Avatar" width="150" style="border-radius: 50%; border: 3px solid #d35400;">
        <p><strong>@{$user['nombre_usuario']}</strong></p>
    </div>

    <form action="usuarios/perfil.php" method="POST" enctype="multipart/form-data" style="flex-grow: 1;">
        <fieldset>
            <legend>Información Personal</legend>
            <div><label>Nombre:</label> <input type="text" name="nombre" value="{$user['nombre']}" required></div>
            <div><label>Apellidos:</label> <input type="text" name="apellidos" value="{$user['apellidos']}" required></div>
            <div><label>Email:</label> <input type="email" name="email" value="{$user['email']}" required></div>
            
            <hr>
            <h3>Cambiar Avatar</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <p style="width: 100%;"><strong>A) Personajes:</strong></p>
                <label><input type="radio" name="avatar_opcion" value="predefinidos/chewbacca.png"><br><img src="img/avatares/predefinidos/chewbacca.png" width="50"></label>
                <label><input type="radio" name="avatar_opcion" value="predefinidos/jake.png"><br><img src="img/avatares/predefinidos/jake.png" width="50"></label>
                <label><input type="radio" name="avatar_opcion" value="predefinidos/moe.png"><br><img src="img/avatares/predefinidos/moe.png" width="50"></label>
                <label><input type="radio" name="avatar_opcion" value="predefinidos/perry.png"><br><img src="img/avatares/predefinidos/perry.png" width="50"></label>
            </div>

            <div style="margin-top: 15px;">
                <p><strong>B) Subir foto:</strong></p>
                <input type="file" name="avatar_subida">
            </div>

            <div style="margin-top: 15px;">
                <p><strong>D) Reset:</strong></p>
                <label><input type="radio" name="avatar_opcion" value="default.png"> Volver al avatar por defecto</label>
            </div>

            <br>
            <button type="submit" style="background: #27ae60; color: white; padding: 8px 15px; cursor:pointer;">Guardar Cambios</button>
        </fieldset>
    </form>
</div>

<div style="margin-top: 40px; padding: 20px; border: 1px solid #ffcccc; background: #fff5f5;">
    <h3 style="color: #c0392b; margin-top: 0;">Zona de Peligro</h3>
    <p>Si eliminas tu cuenta, no podrás recuperar tus BistroCoins ni tus pedidos.</p>
    <form action="usuarios/borrar_cuenta.php" method="POST" onsubmit="return confirm('¿Estás COMPLETAMENTE seguro? Esta acción no se puede deshacer.')">
        <button type="submit" style="background: #e74c3c; color: white; padding: 10px; border: none; cursor: pointer;">
            🗑️ Eliminar mi cuenta permanentemente
        </button>
    </form>
</div>
EOS;

require RAIZ_APP . '/vistas/plantillas/plantilla.php';