<nav id="menu">
    <h3>Navegación</h3>
    <ul>
        <li><a href="<?= RUTA_APP ?>/index.php">Inicio</a></li>
        <li><a href="<?= RUTA_APP ?>/detalles.php">Detalles</a></li>
    </ul>

    <?php if (isset($_SESSION["login"]) && $_SESSION["login"]): ?>
        <h3>Mi Cuenta</h3>
        <ul>
            <li><a href="<?= RUTA_APP ?>/perfil.php">👤 Mi Perfil</a></li>
            <li><a href="<?= RUTA_APP ?>/logout.php">🚪 Cerrar Sesión</a></li>
        </ul>

        <h3>Gestión</h3>
        <ul>
            <?php if ($_SESSION['rol'] == 'gerente'): ?>
                <li style="margin-top: 10px;">
                    <a href="<?= RUTA_APP ?>/admin/usuarios.php" style="color: #d35400; font-weight: bold;">
                        ⚙️ Admin Usuarios (F0)
                    </a>
                    <li><a href="<?= RUTA_APP ?>/admin/productos.php" style="color: #d35400; font-weight: bold;">
                        ⚙️ Admin Productos (F1)
                    </a></li>
                </li>
            <?php endif; ?>
        </ul>
    <?php else: ?>
        <h3>Acceso</h3>
        <ul>
            <li><a href="<?= RUTA_APP ?>/login.php">Login</a></li>
            <li><a href="<?= RUTA_APP ?>/registro.php">Registro</a></li>
        </ul>
    <?php endif; ?>
</nav>