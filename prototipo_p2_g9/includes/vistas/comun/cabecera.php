<header style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; border-bottom: 1px solid #ccc; background-color: #f8f9fa;">
    
    <div style="display: flex; align-items: center; gap: 20px;">
        <div class="logo">
            <img src="<?= RUTA_IMGS ?>logo.jpg" alt="Logo Bistro FDI" width="80" style="vertical-align: middle;">
        </div>
        
        <?php if (isset($_SESSION["login"]) && $_SESSION["login"]): ?>
            <?php 
                // Determinar el color según el rol
                $colorRol = '#16a085'; // Por defecto para cliente u otros
                $rolMayusculas = strtoupper($_SESSION['rol']); 
                
                if ($_SESSION['rol'] === 'gerente') {
                    $colorRol = '#d35400';
                } elseif ($_SESSION['rol'] === 'cocinero') {
                    $colorRol = '#8e44ad';
                } elseif ($_SESSION['rol'] === 'camarero') {
                    $colorRol = '#2980b9';
                }
            ?>
            
            <div style="display: flex; flex-direction: column; justify-content: center;">
                 <span style="font-size: 22px; font-weight: bold; color: #333;"><?= htmlspecialchars($_SESSION["nombre"]) ?></span>
                 <span style="font-size: 16px; font-weight: bold; color: <?= $colorRol ?>; margin-top: 2px;"><?= htmlspecialchars($rolMayusculas) ?></span>
            </div>
            
        <?php endif; ?>
    </div>

    <nav style="display: flex; gap: 10px; align-items: center;">
        
        <?php $estiloBoton = "text-decoration: none; color: #333; background-color: white; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; transition: 0.2s;"; ?>
        
        <?php if (isset($_SESSION["login"]) && $_SESSION["login"] && $_SESSION['rol'] == 'gerente'): ?>
                <a href="<?= RUTA_APP ?>/admin/usuarios.php" style="text-decoration: none; color: #d35400; background-color: white; border: 1px solid #d35400; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">⚙️ Usuarios</a>
                <a href="<?= RUTA_APP ?>/admin/productos.php" style="text-decoration: none; color: #d35400; background-color: white; border: 1px solid #d35400; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">⚙️ Productos</a>
        <?php endif; ?> 

        <a href="<?= RUTA_APP ?>/index.php" style="<?= $estiloBoton ?>">Inicio</a>
        <a href="<?= RUTA_APP ?>/detalles.php" style="<?= $estiloBoton ?>">Detalles</a>

        <?php if (isset($_SESSION["login"]) && $_SESSION["login"]): ?>
            
            <?php if ($_SESSION['rol'] === 'cliente'): ?>
                <a href="<?= RUTA_APP ?>/historial_pedidos.php" style="<?= $estiloBoton ?>">🧾 Mis pedidos</a>
            <?php endif; ?>
            
            <a href="<?= RUTA_APP ?>/perfil.php" style="<?= $estiloBoton ?>">👤 Mi Perfil</a>
            
            <a href="<?= RUTA_APP ?>/logout.php" style="text-decoration: none; color: #c0392b; background-color: #fdf2f0; border: 1px solid #c0392b; padding: 8px 15px; border-radius: 5px; font-size: 14px; margin-left: 10px;">🚪 Salir</a>
            
        <?php else: ?>
            <a href="<?= RUTA_APP ?>/login.php" style="<?= $estiloBoton ?>">Login</a>
            <a href="<?= RUTA_APP ?>/registro.php" style="text-decoration: none; color: white; background-color: black; border: 1px solid black; padding: 8px 15px; border-radius: 5px; font-size: 14px;">Registro</a>
        <?php endif; ?>
        
    </nav>
</header>