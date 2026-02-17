<header>
    <div class="logo">
        <img src="img/logo.jpg" alt="Logo Bistro FDI" width="100">
    </div>
    <div class="saludo">
        <?php
        if (isset($_SESSION["login"]) && $_SESSION["login"]) {
            echo "Bienvenido, " . $_SESSION["nombre"] . ". <a href='logout.php'>(salir)</a>";
        } else {
            echo "Usuario desconocido. <a href='login.php'>Login</a> o <a href='registro.php'>Registro</a>";
        }
        ?>
    </div>
</header>