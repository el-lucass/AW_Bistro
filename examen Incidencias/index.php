<?php
require_once __DIR__.'/includes/config.php';

// Le decimos a PHP que vamos a usar la clase Usuario
use es\ucm\fdi\aw\usuarios\Usuario;

$tituloPagina = 'Inicio - Bistro FDI';
$contenidoPrincipal = "<h1>Bienvenido a Bistro FDI</h1>";

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {

    if (Usuario::tieneRol('cliente')) {
        $contenidoPrincipal .= "
        <div class='seccion-bienvenida'>
            <h2>Tipo de Pedido</h2>
            <p>Selecciona dónde deseas consumir tu pedido</p>
            <div class='grid-opciones'>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/Icono_durum.jpg') . "' alt='Local'>
                    </div>
                    <h3>Consumir en Local</h3>
                    <a href='" . resuelve('/catalogo.php?tipo=local') . "'>
                        <button class='btn-contorno'>Elegir Local</button>
                    </a>
                </div>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/icono_para_llevar.jpg') . "' alt='Para Llevar'>
                    </div>
                    <h3>Para Llevar</h3>
                    <a href='" . resuelve('/catalogo.php?tipo=llevar') . "'>
                        <button class='btn-contorno'>Elegir Para Llevar</button>
                    </a>
                </div>
            </div>
        </div>

        <div class='seccion-bienvenida'>
            <h2>Panel de Control</h2>
            <div class='grid-opciones'>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion icono-pedidos'>
                        <img src='" . resuelve('img/iconos/MisPedidos.png') . "' alt='Mis Pedidos'>
                    </div>
                    <h3>Mis Pedidos</h3>
                    <a href='" . resuelve('/historial_pedidos.php') . "'>
                        <button class='btn-contorno'>Ver Mis Pedidos</button>
                    </a>
                </div>
            </div>
        </div>";
    }

    if (Usuario::tieneRol('gerente')) {
        $contenidoPrincipal .= "
        <div class='seccion-bienvenida'>
            <h2>Panel de Administración</h2>
            <div class='grid-opciones'>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/usuarios.png') . "' alt='Usuarios'>
                    </div>
                    <h3>Usuarios</h3>
                    <a href='" . resuelve('/admin/usuarios.php') . "'>
                        <button class='btn-contorno'>Administrar</button>
                    </a>
                </div>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/Icono_durum.jpg') . "' alt='Productos'>
                    </div>
                    <h3>Productos</h3>
                    <a href='" . resuelve('/admin/productos.php') . "'>
                        <button class='btn-contorno'>Gestionar</button>
                    </a>
                </div>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/MisPedidos.png') . "' alt='Pedidos'>
                    </div>
                    <h3>Pedidos</h3>
                    <a href='" . resuelve('/admin/pedidos_pendientes.php') . "'>
                        <button class='btn-contorno'>Ver pedidos</button>
                    </a>
                </div>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/icono_para_llevar.jpg') . "' alt='Ofertas'>
                    </div>
                    <h3>Ofertas</h3>
                    <a href='" . resuelve('/admin/ofertas.php') . "'>
                        <button class='btn-contorno'>Gestionar</button>
                    </a>
                </div>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/Recompensas.png') . "' alt='Recompensas'>
                    </div>
                    <h3>Recompensas</h3>
                    <a href='" . resuelve('/admin/recompensas.php') . "'>
                        <button class='btn-contorno'>Gestionar</button>
                    </a>
                </div>
            </div>
        </div>";
    }

    if (Usuario::tieneRol('cocinero')) {
        $contenidoPrincipal .= "
        <div class='seccion-bienvenida'>
            <h2>Panel de Cocina</h2>
            <div class='grid-opciones'>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/Icono_durum.jpg') . "' alt='Cocina'>
                    </div>
                    <h3>Pedidos pendientes</h3>
                    <a href='" . resuelve('cocinero/cocinero_pedidos.php') . "'>
                        <button class='btn-contorno'>Ver pedidos</button>
                    </a>
                </div>
            </div>
        </div>";
    }

    if (Usuario::tieneRol('camarero')) {
        $contenidoPrincipal .= "
        <div class='seccion-bienvenida'>
            <h2>Panel de Camarero</h2>
            <div class='grid-opciones'>
                <div class='tarjeta-opcion'>
                    <div class='icono-opcion'>
                        <img src='" . resuelve('img/iconos/MisPedidos.png') . "' alt='Camarero'>
                    </div>
                    <h3>Pedidos Camarero</h3>
                    <a href='" . resuelve('/camarero/camarero_pedidos.php') . "'>
                        <button class='btn-contorno'>Ver pedidos</button>
                    </a>
                </div>
            </div>
        </div>";
    }

} else {
    $contenidoPrincipal .= "
    <p>Por favor, identifícate para gestionar tus pedidos.</p>
    <a href='" . resuelve('/login.php') . "'><button class='btn-lg'>Iniciar Sesión</button></a>
    <a href='" . resuelve('/registro.php') . "'><button class='btn-contorno btn-lg ml-10'>Registrarse</button></a>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
