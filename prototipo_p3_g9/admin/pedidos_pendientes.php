<?php
require_once __DIR__ . '/../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\cocinero\Cocina;

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

$contenidoPrincipal = "<div style='max-width: 1100px; margin: 0 auto; padding: 20px;'>
<h1 style='margin-top:0;'>Pedidos pendientes</h1>

<div style='border:1px solid #eee; border-radius:6px; overflow:hidden; background:#fff;'>";

if (empty($pedidos)) {
    $contenidoPrincipal .= "<div style='padding:20px; color:#666;'>No hay pedidos pendientes.</div>";
} else {

    $contenidoPrincipal .= "
    <table style='width:100%; border-collapse:collapse; font-size:14px;'>
      <thead>
        <tr style='background:#fafafa; border-bottom:1px solid #eee;'>
          <th style='padding:12px; text-align:left;'>#</th>
          <th style='padding:12px; text-align:left;'>Fecha</th>
          <th style='padding:12px; text-align:left;'>Estado</th>
          <th style='padding:12px; text-align:left;'>Cliente</th>
          <th style='padding:12px; text-align:left;'>Cocinero</th>
          <th style='padding:12px; text-align:center;'>Detalle</th>
        </tr>
      </thead>
      <tbody>";

    foreach ($pedidos as $p) {
        $num = htmlspecialchars($p['numero_dia']);
        $fecha = date('d/m/Y H:i', strtotime($p['fecha_hora']));
        $estado = htmlspecialchars($p['estado']);
        $cliente = htmlspecialchars($p['cliente_user']);

        // Avatar cocinero si existe
        $cocineroHtml = "<span style='color:#999;'>Sin asignar</span>";

        if (!empty($p['cocinero_user'])) {
            $avatar = htmlspecialchars($p['cocinero_avatar'] ?? 'default.png');
            $avatarSrc = rutaAvatar($avatar);

            $cocineroHtml = "
                <div style='display:flex; align-items:center; gap:10px;'>
                    <img src='{$avatarSrc}' 
                         style='width:32px; height:32px; border-radius:50%; object-fit:cover; border:1px solid #ddd;'>
                    <span>".htmlspecialchars($p['cocinero_user'])."</span>
                </div>";
        }

        $contenidoPrincipal .= "
        <tr style='border-bottom:1px solid #f2f2f2;'>
          <td style='padding:12px;'>#{$num}</td>
          <td style='padding:12px;'>{$fecha}</td>
          <td style='padding:12px;'>{$estado}</td>
          <td style='padding:12px;'>{$cliente}</td>
          <td style='padding:12px;'>{$cocineroHtml}</td>
          <td style='padding:12px; text-align:center;'>
            <a href='pedido_pendiente.php?id={$p['id']}'>
              <button style='padding:8px 12px; background:black; color:white; border:none; border-radius:5px; cursor:pointer;'>
                Ver
              </button>
            </a>
          </td>
        </tr>";
    }

    $contenidoPrincipal .= "</tbody></table>";
}

$contenidoPrincipal .= "</div></div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';