<?php
// ¡FUERA EL REQUIRE_ONCE! Ya no existe usuarios.php
// Ahora le decimos que vamos a usar la clase Usuario de nuestro namespace
use es\ucm\fdi\aw\usuarios\Usuario;

// Verificamos de forma segura si el usuario está logueado
$estaLogueado = isset($_SESSION["login"]) && $_SESSION["login"] === true;
$rolActual = $estaLogueado && isset($_SESSION['rol']) ? $_SESSION['rol'] : 'visitante';
$nombreActual = $estaLogueado && isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';
?>

<header class="cabecera-nav">

    <div class="cabecera-izq">
        <div class="logo">
            <img src="<?= RUTA_IMGS ?>logo.png" alt="Logo Bistro FDI" width="80">
        </div>

        <?php if ($estaLogueado): ?>
            <div class="cabecera-usuario">
                <span class="cabecera-nombre"><?= htmlspecialchars($nombreActual) ?></span>
                <span class="cabecera-rol rol-<?= $rolActual ?>"><?= strtoupper($rolActual) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <nav class="cabecera-der">

        <?php if (Usuario::tieneRol('gerente')): ?>
            <a href="<?= RUTA_APP ?>/admin/usuarios.php"             class="nav-link-admin">Usuarios</a>
            <a href="<?= RUTA_APP ?>/admin/productos.php"            class="nav-link-admin">Productos</a>
            <a href="<?= RUTA_APP ?>/admin/pedidos_pendientes.php"   class="nav-link-admin">Pedidos pendientes</a>
            <a href="<?= RUTA_APP ?>/admin/ofertas.php"              class="nav-link-admin">Ofertas</a>
            <a href="<?= RUTA_APP ?>/admin/recompensas.php"              class="nav-link-admin">Recompensas</a>
            <span class="nav-separador"></span>
        <?php endif; ?>

        <a href="<?= RUTA_APP ?>/index.php" class="nav-link">Inicio</a>

        <?php if ($estaLogueado): ?>

            <?php if (Usuario::tieneRol('cliente')): ?>
                <a href="<?= RUTA_APP ?>/historial_pedidos.php"   class="nav-link">Mis pedidos</a>
                <a href="<?= RUTA_APP ?>/ofertas_disponibles.php" class="nav-link">Ofertones</a>
            <?php endif; ?>

            <?php if (Usuario::tieneRol('cocinero')): ?>
                <a href="<?= RUTA_APP ?>/cocinero/cocinero_pedidos.php" class="nav-link">Pedidos pendientes</a>
            <?php endif; ?>

            <?php if (Usuario::tieneRol('camarero')): ?>
                <a href="<?= RUTA_APP ?>/camarero/camarero_pedidos.php" class="nav-link">Pedidos Camarero</a>
            <?php endif; ?>

            <a href="<?= RUTA_APP ?>/perfil.php" class="nav-link">Mi Perfil</a>
            <a href="<?= RUTA_APP ?>/logout.php" class="nav-link-salir">Salir</a>

        <?php else: ?>
            <a href="<?= RUTA_APP ?>/login.php"   class="nav-link">Login</a>
            <a href="<?= RUTA_APP ?>/registro.php" class="nav-link-registro">Registro</a>
        <?php endif; ?>

    </nav>
</header>
