<?php
require_once 'includes/config.php';

$tituloPagina = 'Inicio - Bistro FDI';

// Preparamos el contenido principal
$contenidoPrincipal = "<h1>Bienvenido a Bistro FDI</h1>";

if (isset($_SESSION['login']) && $_SESSION['login']) {
    $contenidoPrincipal .= "<p>Hola, <strong>{$_SESSION['nombre']}</strong> (Rol: {$_SESSION['rol']})</p>";
    $contenidoPrincipal .= "<a href='logout.php'><button>Cerrar Sesión</button></a>";
    
    $contenidoPrincipal .= "<h2>Panel de Control</h2>";
    $contenidoPrincipal .= "<div class='menu-botones'>";
    
    // Botones de las funcionalidades (sin implementar aún)
    $contenidoPrincipal .= "<button onclick=\"location.href='productos.php'\">F1: Gestión Productos</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='pedidos.php'\">F2: Gestión Pedidos</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='cocina.php'\">F3: Vista Cocina</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='notificaciones.php'\">F4: Notificaciones</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='recompensas.php'\">F5: Recompensas</button> ";
    
    // Solo el gerente ve el botón de administración de usuarios (F0)
    if ($_SESSION['rol'] == 'gerente') {
        $contenidoPrincipal .= "<br><br><button style='background-color: orange;' onclick=\"location.href='admin/usuarios.php'\">F0: Administrar Usuarios</button>";
    }
    
    $contenidoPrincipal .= "</div>";
} else {
    $contenidoPrincipal .= "<p>Por favor, identifícate para gestionar tus pedidos.</p>";
    $contenidoPrincipal .= "<a href='login.php'><button>Iniciar Sesión</button></a> ";
    $contenidoPrincipal .= "<a href='registro.php'><button>Registrarse</button></a>";
}

// Cargamos la plantilla
require RAIZ_APP . '/vistas/plantillas/plantilla.php';