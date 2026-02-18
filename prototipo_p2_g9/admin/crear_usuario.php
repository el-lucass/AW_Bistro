<?php
require_once '../includes/config.php'; // Correcto: subes un nivel para encontrar config
$tituloPagina = 'Crear Usuario - Administrador';

$contenidoPrincipal = <<<EOS
<h1>Crear Nuevo Usuario</h1>
<form action="../usuarios/admin_crear.php" method="POST">
    <fieldset>
        <legend>Datos del nuevo usuario</legend>
        <div><label>Username:</label> <input type="text" name="nombre_usuario" required></div>
        <div><label>Email:</label> <input type="email" name="email" required></div>
        <div><label>Nombre:</label> <input type="text" name="nombre" required></div>
        <div><label>Apellidos:</label> <input type="text" name="apellidos" required></div>
        <div><label>Contraseña:</label> <input type="password" name="password" required></div>
        <div><label>Rol:</label> 
            <select name="rol">
                <option value="cliente">Cliente</option>
                <option value="camarero">Camarero</option>
                <option value="cocinero">Cocinero</option>
                <option value="gerente">Gerente</option>
            </select>
        </div>
        <br>
        <button type="submit">Crear Usuario</button>
    </fieldset>
</form>
EOS;

// Se queda igual porque RAIZ_APP es una ruta absoluta definida en config.php
require RAIZ_APP . '/vistas/plantillas/plantilla.php';