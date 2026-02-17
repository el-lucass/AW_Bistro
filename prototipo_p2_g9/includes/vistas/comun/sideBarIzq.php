<nav id="menu">
    <h3>Navegación</h3>
    <ul>
        <li><a href="index.php">Inicio</a></li>
        <li><a href="detalles.php">Detalles</a></li>
        <li><a href="miembros.php">Miembros</a></li>
        <li><a href="contacto.php">Contacto</a></li>
        <?php if (isset($_SESSION["rol"]) && $_SESSION["rol"] == 'gerente'): ?>
            <li><a href="admin_usuarios.php">Admin Usuarios (F0)</a></li>
        <?php endif; ?>
    </ul>
</nav>