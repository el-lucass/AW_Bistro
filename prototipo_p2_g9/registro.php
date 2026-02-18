<?php
require_once 'includes/config.php';

$tituloPagina = 'Registro de Usuario - Bistro FDI';

$contenidoPrincipal = <<<EOS
    <h1>Únete a Bistro FDI</h1>
    <p>Regístrate para realizar pedidos y acumular BistroCoins.</p>
    <form action="usuarios/registro.php" method="POST">
        <fieldset>
            <legend>Datos Personales</legend>
            <div><label>Nombre de usuario:</label> <input type="text" name="nombre_usuario" required></div>
            <div><label>Email:</label> <input type="email" name="email" required></div>
            <div><label>Nombre:</label> <input type="text" name="nombre" required></div>
            <div><label>Apellidos:</label> <input type="text" name="apellidos" required></div>
            <div><label>Contraseña:</label> <input type="password" name="password" required></div>
            <br>
            <button type="submit">Crear cuenta</button>
        </fieldset>
    </form>
EOS;

// Usamos la ruta corregida (sin /includes/)
require RAIZ_APP . '/vistas/plantillas/plantilla.php';