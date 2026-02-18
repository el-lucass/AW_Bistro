<header>
    <div class="logo">
        <img src="<?= RUTA_IMGS ?>logo.jpg" alt="Logo Bistro FDI" width="100">
    </div>
    <div class="saludo">
        <?php
        if (isset($_SESSION["login"]) && $_SESSION["login"]) {
            // El enlace ahora será /prototipo_p2_g9/logout.php
            echo "Bienvenido, " . $_SESSION["nombre"] . ". <a href='" . RUTA_APP . "/logout.php'>(salir)</a>";
        } else {
            echo "Usuario desconocido. <a href='" . RUTA_APP . "/login.php'>Login</a> o <a href='" . RUTA_APP . "/registro.php'>Registro</a>";
        }
        ?>
    </div>
</header>