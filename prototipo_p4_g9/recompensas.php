<?php
require_once __DIR__.'/includes/config.php';

use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\recompensas\Recompensa;
use es\ucm\fdi\aw\productos\Producto;

if (!isset($_SESSION['login']) || !Usuario::tieneRol('cliente')) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['carrito'])) {
    header('Location: index.php');
    exit();
}

$saldo = Usuario::getBistrocoins($_SESSION['id']);
$bistrocoinsCarrito = 0;

foreach ($_SESSION['carrito']['productos'] as $item) {
    if (!empty($item['es_recompensa'])) {
        $bistrocoinsCarrito += $item['bistrocoins'] * $item['cantidad'];
    }
}
$saldoRestante = $saldo - $bistrocoinsCarrito;
$recompensas = Recompensa::listaRecompensasActivas();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_recompensa'])) {
    $idRecompensa = (int)$_POST['id_recompensa'];
    $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));

    $recompensa = Recompensa::buscaRecompensa($idRecompensa);

    if ($recompensa && $recompensa->getActiva()) {
        $costeTotal = $recompensa->getBistrocoins() * $cantidad;

        $bistrocoinsYaUsadas = 0;

        foreach ($_SESSION['carrito']['productos'] as $item) {
            if (!empty($item['es_recompensa'])) {
                $bistrocoinsYaUsadas += $item['bistrocoins'] * $item['cantidad'];
            }
        }

        $saldoDisponible = $saldo - $bistrocoinsYaUsadas;

        if ($saldoDisponible >= $costeTotal) {
            $encontrada = false;

            foreach ($_SESSION['carrito']['productos'] as &$item) {
            if (
                $item['id_producto'] == $recompensa->getIdProducto()
                && !empty($item['es_recompensa'])
            ) {
                $item['cantidad'] += $cantidad;
                 $encontrada = true;
                break;
        }
    }
    unset($item);

    if (!$encontrada) {
        $_SESSION['carrito']['productos'][] = [
            'id_producto' => $recompensa->getIdProducto(),
            'nombre' => $recompensa->getNombreProducto(),
            'precio' => 0,
            'cantidad' => $cantidad,
            'es_recompensa' => true,
            'bistrocoins' => $recompensa->getBistrocoins()
        ];
    }
            $_SESSION['mensaje_ok_recompensa'] = "Recompensa añadida al carrito.";
        } else {
            $_SESSION['mensaje_error_recompensa'] = "No tienes suficientes BistroCoins para añadir esa recompensa.";
        }
    }

    header('Location: recompensas.php');
    exit();
}

$tituloPagina = 'Recompensas - Bistro FDI';

$mensaje = '';

if (isset($_SESSION['mensaje_error_recompensa'])) {
    $mensaje = "
    <div class='alerta-error mb-20'>
        " . htmlspecialchars($_SESSION['mensaje_error_recompensa']) . "
    </div>";
    unset($_SESSION['mensaje_error_recompensa']);
}

if (isset($_SESSION['mensaje_ok_recompensa'])) {
    $mensaje = "
    <div class='alerta-ok mb-20'>
        " . htmlspecialchars($_SESSION['mensaje_ok_recompensa']) . "
    </div>";
    unset($_SESSION['mensaje_ok_recompensa']);
}

$cantidadCarrito = 0;
foreach ($_SESSION['carrito']['productos'] as $item) {
    $cantidadCarrito += $item['cantidad'];
}

$contenidoPrincipal = "
<div class='catalogo-header'>
    <div>
        <a href='catalogo.php' class='nav-link'>← Volver al catálogo</a>
        <h1 class='mb-0'>Recompensas disponibles</h1>
        <p class='catalogo-tipo'>
        Tienes <strong>{$saldo} BistroCoins</strong><br>
        Vas a gastar <strong>{$bistrocoinsCarrito} BistroCoins</strong><br>
        Te quedarán <strong>{$saldoRestante} BistroCoins</strong>
    </p>
    </div>
    <div>
        <a href='carrito.php' class='nav-link'>🛒 Ver Carrito ({$cantidadCarrito})</a>
    </div>
</div>

{$mensaje}

<div class='productos-grid'>";

foreach ($recompensas as $r) {
    $producto = Producto::buscaProducto($r->getIdProducto());

    $imgPrincipal = $producto ? $producto->getImagenPrincipal() : null;
    $rutaImg = $imgPrincipal
        ? RUTA_APP . "/img/productos/{$imgPrincipal}"
        : RUTA_APP . "/img/productos/default_food.png";

    $puede = $saldo >= $r->getBistrocoins();
    $disabled = $puede ? "" : "disabled";
    $textoBoton = $puede ? "Añadir recompensa" : "Saldo insuficiente";

    $contenidoPrincipal .= "
    <div class='producto-card'>
        <div>
            <img src='{$rutaImg}' class='producto-imagen' alt='" . htmlspecialchars($r->getNombreProducto()) . "'>
            <h3 class='producto-nombre'>" . htmlspecialchars($r->getNombreProducto()) . "</h3>
            <p class='producto-descripcion'>Canjea este producto usando tus BistroCoins.</p>
            <h2 class='producto-precio'>{$r->getBistrocoins()} BistroCoins</h2>
        </div>

        <form method='POST' class='producto-form'>
            <input type='hidden' name='id_recompensa' value='{$r->getId()}'>
            <input type='number' name='cantidad' value='1' min='1' class='producto-cantidad'>
            <button type='submit' class='btn-anadir' {$disabled}>{$textoBoton}</button>
        </form>
    </div>";
}

$contenidoPrincipal .= "</div>";

require RAIZ_APP . '/vistas/plantillas/plantilla.php';
?>