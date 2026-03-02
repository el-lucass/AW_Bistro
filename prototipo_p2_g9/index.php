<?php
require_once 'includes/config.php';

$tituloPagina = 'Inicio - Bistro FDI';

// Preparamos el contenido principal
$contenidoPrincipal = "<h1>Bienvenido a Bistro FDI</h1>";

if (isset($_SESSION['login']) && $_SESSION['login']) {

    // Verificamos si es cliente para mostrarle las opciones de pedido
    if ($_SESSION['rol'] == 'cliente') {
        
        // Aquí definimos tu estilo para usarlo en los dos botones
        $estiloBoton = "text-decoration: none; color: #333; background-color: white; border: 1px solid #bbb; padding: 8px 15px; border-radius: 5px; font-size: 14px; transition: 0.2s; margin-top: 15px; cursor: pointer;";
        
        $contenidoPrincipal .= "<div style='text-align: center; margin-top: 40px; margin-bottom: 40px;'>";
        $contenidoPrincipal .= "<h2>Tipo de Pedido</h2>";
        $contenidoPrincipal .= "<p>Selecciona dónde deseas consumir tu pedido</p>";
        
        $contenidoPrincipal .= "<div style='display: flex; justify-content: center; gap: 30px; margin-top: 20px;'>";

        // Opción 1: Consumir en Local (Usando Icono_durum.jpg)
        $contenidoPrincipal .= "<div style='border: 1px solid #ccc; padding: 30px; width: 250px;'>";
        $contenidoPrincipal .= "<div style='margin-bottom: 15px;'>";
        $contenidoPrincipal .= "<img src='img/iconos/Icono_durum.jpg' alt='Local' style='width: 60px; height: 60px; object-fit: contain;'>";
        $contenidoPrincipal .= "</div>";
        $contenidoPrincipal .= "<h3>Consumir en Local</h3>";
        $contenidoPrincipal .= "<p>Para disfrutar tu pedido en las instalaciones de Bistro FDI</p>";
        $contenidoPrincipal .= "<a href='catalogo.php?tipo=local'><button style='{$estiloBoton}'>Elegir Local</button></a>";
        $contenidoPrincipal .= "</div>";
        
        // Opción 2: Para Llevar (Usando icono_para_llevar.jpg)
        $contenidoPrincipal .= "<div style='border: 1px solid #ccc; padding: 30px; width: 250px;'>";
        $contenidoPrincipal .= "<div style='margin-bottom: 15px;'>";
        $contenidoPrincipal .= "<img src='img/iconos/icono_para_llevar.jpg' alt='Para Llevar' style='width: 60px; height: 60px; object-fit: contain;'>";
        $contenidoPrincipal .= "</div>";
        $contenidoPrincipal .= "<h3>Para Llevar</h3>";
        $contenidoPrincipal .= "<p>Para recoger y consumir fuera de Bistro FDI</p>";
        $contenidoPrincipal .= "<a href='catalogo.php?tipo=llevar'><button style='{$estiloBoton}'>Elegir Para Llevar</button></a>";
        $contenidoPrincipal .= "</div>";
        
        $contenidoPrincipal .= "</div>"; 
        $contenidoPrincipal .= "</div>"; 
    }


    $contenidoPrincipal .= "<h2>Panel de Control</h2>";
    $contenidoPrincipal .= "<div class='menu-botones'>";
    
    // Botones de las funcionalidades (sin implementar aún)
    $contenidoPrincipal .= "<button onclick=\"location.href='pedidos.php'\">F2: Gestión Pedidos</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='cocinero/cocinero_pedidos.php'\">F3: Vista Cocina</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='notificaciones.php'\">F4: Notificaciones</button> ";
    $contenidoPrincipal .= "<button onclick=\"location.href='recompensas.php'\">F5: Recompensas</button> ";
    
    // Solo el gerente ve el botón de administración de usuarios (F0)
    if ($_SESSION['rol'] == 'gerente') {
        $contenidoPrincipal .= "<br><br><button style='background-color: orange;' onclick=\"location.href='admin/usuarios.php'\">F0: Administrar Usuarios</button>";
        $contenidoPrincipal .= "<button style='background-color: orange;' onclick=\"location.href='admin/productos.php'\">F1: Gestión Productos</button> ";
        $contenidoPrincipal .= "<button style='background-color: orange;' onclick=\"location.href='admin/pedidos_pendientes.php'\">F3: Pedidos pendientes</button> ";
    }
    
    $contenidoPrincipal .= "</div>";
} else {
    $contenidoPrincipal .= "<p>Por favor, identifícate para gestionar tus pedidos.</p>";
    $contenidoPrincipal .= "<a href='login.php'><button>Iniciar Sesión</button></a> ";
    $contenidoPrincipal .= "<a href='registro.php'><button>Registrarse</button></a>";
}

// Cargamos la plantilla
require RAIZ_APP . '/vistas/plantillas/plantilla.php';