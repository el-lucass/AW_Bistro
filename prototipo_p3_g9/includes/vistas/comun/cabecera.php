<?php
// ¡FUERA EL REQUIRE_ONCE! Ya no existe usuarios.php
// Ahora le decimos que vamos a usar la clase Usuario de nuestro namespace
use es\ucm\fdi\aw\usuarios\Usuario; 

// Verificamos de forma segura si el usuario está logueado
$estaLogueado = isset($_SESSION["login"]) && $_SESSION["login"] === true;
$rolActual = $estaLogueado && isset($_SESSION['rol']) ? $_SESSION['rol'] : 'visitante';
$nombreActual = $estaLogueado && isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';
?>

<header style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; border-bottom: 1px solid #ccc; background-color: #f8f9fa;">
    
    <div style="display: flex; align-items: center; gap: 20px;">
        <div class="logo">
            <img src="<?= RUTA_IMGS ?>logo.jpg" alt="Logo Bistro FDI" width="80" style="vertical-align: middle;">
        </div>
        
        <?php if ($estaLogueado): ?>
            <?php 
                // Determinar el color según el rol usando la variable segura $rolActual
                $colorRol = '#16a085'; // Por defecto para cliente u otros
                $rolMayusculas = strtoupper($rolActual); 
                
                if ($rolActual === 'gerente') {
                    $colorRol = '#d35400';
                } elseif ($rolActual === 'cocinero') {
                    $colorRol = '#8e44ad';
                } elseif ($rolActual === 'camarero') {
                    $colorRol = '#2980b9';
                }
            ?>
            
            <div style="display: flex; flex-direction: column; justify-content: center;">
                 <span style="font-size: 22px; font-weight: bold; color: #333;"><?= htmlspecialchars($nombreActual) ?></span>
                 <span style="font-size: 16px; font-weight: bold; color: <?= $colorRol ?>; margin-top: 2px;"><?= htmlspecialchars($rolMayusculas) ?></span>
            </div>
            
        <?php endif; ?>
    </div>

    <nav style="display: flex; gap: 10px; align-items: center;">
        
        <?php $estiloBoton = "text-decoration: none; color: #333; background-color: white; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; transition: 0.2s;"; ?>
        
        <?php if (Usuario::tieneRol('gerente')): ?>
                <a href="<?= RUTA_APP ?>/admin/usuarios.php" style="text-decoration: none; color: #d35400; background-color: white; border: 1px solid #d35400; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">⚙️ Usuarios</a>
                <a href="<?= RUTA_APP ?>/admin/productos.php" style="text-decoration: none; color: #d35400; background-color: white; border: 1px solid #d35400; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">⚙️ Productos</a>
                <a href="<?= RUTA_APP ?>/admin/pedidos_pendientes.php" style="text-decoration: none; color: #d35400; background-color: white; border: 1px solid #d35400; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">⚙️ Pedidos pendientes</a>
                <a href="<?= RUTA_APP ?>/admin/ofertas.php" style="text-decoration: none; color: #d35400; background-color: white; border: 1px solid #d35400; padding: 8px 15px; border-radius: 5px; font-size: 14px; font-weight: bold;">⚙️ Ofertas</a>
        <?php endif; ?> 

        <a href="<?= RUTA_APP ?>/index.php" style="<?= $estiloBoton ?>">Inicio</a>

        <?php if ($estaLogueado): ?>
            
            <?php if (Usuario::tieneRol('cliente')): ?>
                <a href="<?= RUTA_APP ?>/historial_pedidos.php" style="<?= $estiloBoton ?>">🧾 Mis pedidos</a>
                <a href="<?= RUTA_APP ?>/ofertas_disponibles.php" style="<?= $estiloBoton ?>">🔥 Ofertones!</a>
            <?php endif; ?>
                
            <?php if (Usuario::tieneRol('cocinero')): ?>
                <a href="<?= RUTA_APP ?>/cocinero/cocinero_pedidos.php" style="<?= $estiloBoton ?>">🧾 Pedidos pendientes</a>
            <?php endif; ?>

            <?php if (Usuario::tieneRol('camarero')): ?>
                <a href="<?= RUTA_APP ?>/camarero/camarero_pedidos.php" style="<?= $estiloBoton ?>">🧾 Vista Camarero</a>
            <?php endif; ?>
            
            <a href="<?= RUTA_APP ?>/perfil.php" style="<?= $estiloBoton ?>">👤 Mi Perfil</a>
            
            <a href="<?= RUTA_APP ?>/logout.php" style="text-decoration: none; color: #c0392b; background-color: #fdf2f0; border: 1px solid #c0392b; padding: 8px 15px; border-radius: 5px; font-size: 14px; margin-left: 10px;">🚪 Salir</a>
            
        <?php else: ?>
            <a href="<?= RUTA_APP ?>/login.php" style="<?= $estiloBoton ?>">Login</a>
            <a href="<?= RUTA_APP ?>/registro.php" style="text-decoration: none; color: white; background-color: black; border: 1px solid black; padding: 8px 15px; border-radius: 5px; font-size: 14px;">Registro</a>
        <?php endif; ?>
        
    </nav>
</header> 