<?php
require_once '../includes/config.php';

// Importamos las clases necesarias
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\pedidos\Pedido;

// 1. Seguridad básica: hay que estar logueado
if (!isset($_SESSION['login'])) {
    header('Location: ../login.php');
    exit;
}

// 2. Comprobamos que la petición viene por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_pedido = $_POST['id_pedido'] ?? null;
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    $redirigir = $_POST['redirigir'] ?? '../index.php'; // Por defecto al inicio si no se especifica

    if ($id_pedido && $nuevo_estado) {
        
        // 3. SEGURIDAD SEGÚN EL ROL
        if ($_SESSION['rol'] === 'cliente') {
            // Un cliente SOLO puede cancelar, y SOLO si el pedido es suyo y está 'recibido'
            if ($nuevo_estado === 'cancelado') {
                $pedido = Pedido::buscaPedido($id_pedido);
                
                // Verificamos propiedad y estado
                if ($pedido && $pedido->getIdUsuario() == $_SESSION['id'] && $pedido->getEstado() === 'recibido') {
                    Pedido::actualizaEstadoPedido($id_pedido, $nuevo_estado);
                }
            }
        } 
        elseif ($_SESSION['rol'] === 'gerente' || $_SESSION['rol'] === 'cocinero') {
            // El personal del restaurante tiene permiso para cambiar el estado libremente
            Pedido::actualizaEstadoPedido($id_pedido, $nuevo_estado);   
        }

        // 4. Redirigimos de vuelta a donde estábamos (ej: historial_pedidos.php)
        header("Location: $redirigir");
        exit;
    }
}

// Si alguien intenta acceder directamente por URL, lo mandamos al inicio
header('Location: ../index.php');
exit;