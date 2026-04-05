<?php
require_once __DIR__ . '/../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\cocineros\Cocina;

// Función para visualizar el avatar bien
function rutaAvatar($avatarFile) {
    $avatarFile = $avatarFile ?: 'default.png';
    $base = __DIR__ . '/../img/avatares/';

    if (file_exists($base . 'usuarios/' . $avatarFile)) {
        return '../img/avatares/usuarios/' . $avatarFile;
    }

    if (file_exists($base . 'predefinidos/' . $avatarFile)) {
        return '../img/avatares/predefinidos/' . $avatarFile;
    }

    if (file_exists($base . $avatarFile)) {
        return '../img/avatares/' . $avatarFile;
    }

    return '../img/avatares/default.png';
}

// Seguridad: solo admin usando el método de Usuario
if (!Usuario::tieneRol('gerente')) {
    header('Location: ../login.php');
    exit();
}

$tituloPagina = 'Pedidos pendientes';

// LLAMADA ESTÁTICA: Usamos el método de la clase Cocina
$pedidos = Cocina::listaPedidosPendientesGerente();

$contenidoPrincipal = "<h1 class='mt-0'>Pedidos pendientes</h1>";

if (empty($pedidos)) {
    $contenidoPrincipal .= "<div class='panel-tabla'><div class='panel-vacio'>No hay pedidos pendientes.</div></div>";
} else {
    $contenidoPrincipal .= "
    <div class='panel-tabla'>
    <table>
        <thead><tr>
            <th>#</th><th>Fecha</th><th>Estado</th><th>Cliente</th><th>Cocinero</th>
            <th class='texto-centro'>Detalle</th>
        </tr></thead>
        <tbody>";

    foreach ($pedidos as $p) {
        $num    = htmlspecialchars($p['numero_dia']);
        $fecha  = date('d/m/Y H:i', strtotime($p['fecha_hora']));
        $estado = htmlspecialchars($p['estado']);
        $cliente = htmlspecialchars($p['cliente_user']);

        $cocineroHtml = "<span class='cocinero-sin-asignar'>Sin asignar</span>";
        if (!empty($p['cocinero_user'])) {
            $avatarSrc = rutaAvatar(htmlspecialchars($p['cocinero_avatar'] ?? 'default.png'));
            $cocineroHtml = "
                <div class='flex-fila gap-10'>
                    <img src='{$avatarSrc}' class='avatar-mini'>
                    <span>" . htmlspecialchars($p['cocinero_user']) . "</span>
                </div>";
        }

        $contenidoPrincipal .= "
        <tr>
            <td>#{$num}</td>
            <td>{$fecha}</td>
            <td>{$estado}</td>
            <td>{$cliente}</td>
            <td>{$cocineroHtml}</td>
            <td class='texto-centro'>
                <a href='pedido_pendiente.php?id={$p['id']}'>
                    <button class='btn-oscuro btn-sm'>Ver</button>
                </a>
            </td>
        </tr>";
    }

    $contenidoPrincipal .= "</tbody></table></div>";
}

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
