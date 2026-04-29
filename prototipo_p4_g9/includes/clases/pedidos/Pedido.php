<?php
namespace es\ucm\fdi\aw\pedidos;

use es\ucm\fdi\aw\Aplicacion;

class Pedido
{
    // Propiedades privadas del Pedido
    private $id;
    private $id_usuario;
    private $nombre_usuario; 
    private $avatar_usuario; 
    private $numero_dia;
    private $tipo;
    private $total_sin_descuento; 
    private $descuento_aplicado;  
    private $total_iva;      
    private $estado;
    private $fecha_hora;

    //Constructor privado 
    private function __construct($id, $id_usuario, $numero_dia, $tipo, $total_sin_descuento, $descuento_aplicado, $total_iva, $estado, $fecha_hora, $nombre_usuario = null, $avatar_usuario = null)
    {
        $this->id = $id;
        $this->id_usuario = $id_usuario;
        $this->numero_dia = $numero_dia;
        $this->tipo = $tipo;
        $this->total_sin_descuento = $total_sin_descuento;
        $this->descuento_aplicado = $descuento_aplicado;
        $this->total_iva = $total_iva;
        $this->estado = $estado;
        $this->fecha_hora = $fecha_hora;
        $this->nombre_usuario = $nombre_usuario;
        $this->avatar_usuario = $avatar_usuario;
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getIdUsuario() { return $this->id_usuario; }
    public function getNombreUsuario() { return $this->nombre_usuario; }
    public function getAvatarUsuario() { return $this->avatar_usuario; }
    public function getNumeroDia() { return $this->numero_dia; }
    public function getTipo() { return $this->tipo; }
    public function getTotalSinDescuento() { return $this->total_sin_descuento; } 
    public function getDescuentoAplicado() { return $this->descuento_aplicado; }   
    public function getTotalIva() { return $this->total_iva; }
    public function getEstado() { return $this->estado; }
    public function getFechaHora() { return $this->fecha_hora; }



    //Crea un pedido nuevo con sus productos mediante una transacción.
    public static function creaPedido($id_usuario, $tipo, $total_sin_descuento, $descuento_aplicado, $total_iva, $carrito, $estado = 'recibido')
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $conn->begin_transaction();

        try {
            // Calcular el número de pedido del día
            $sql_num = "SELECT MAX(numero_dia) as max_dia FROM pedidos WHERE DATE(fecha_hora) = CURDATE()";
            $result_num = $conn->query($sql_num);
            $row_num = $result_num->fetch_assoc();

            $numero_dia = 1; 
            if ($row_num['max_dia'] !== null) {
                $numero_dia = $row_num['max_dia'] + 1;
            }

            // Insertar el pedido en la tabla principal (Nombres de columnas de tu BD)
            $stmt = $conn->prepare("INSERT INTO pedidos (id_usuario, numero_dia, tipo, total_sin_descuento, descuento_aplicado, total_iva, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisddds", $id_usuario, $numero_dia, $tipo, $total_sin_descuento, $descuento_aplicado, $total_iva, $estado);
            $stmt->execute();
            
            $id_pedido = $conn->insert_id;
            
            // Insertar cada línea de producto
            $stmt_prod = $conn->prepare("INSERT INTO pedido_productos (id_pedido, id_producto, cantidad, precio_unitario_historico, es_recompensa) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($carrito['productos'] as $item) {
                $id_producto = $item['id_producto'];
                $cantidad = $item['cantidad'];
                $precio = $item['precio'];
                $esRecompensa = !empty($item['es_recompensa']) ? 1 : 0;

                $stmt_prod->bind_param("iiidi", $id_pedido, $id_producto, $cantidad, $precio, $esRecompensa);
                $stmt_prod->execute();
            }
            
            // Obtener la fecha_hora generada
            $stmt_fecha = $conn->prepare("SELECT fecha_hora FROM pedidos WHERE id = ?");
            $stmt_fecha->bind_param("i", $id_pedido);
            $stmt_fecha->execute();
            $row_fecha = $stmt_fecha->get_result()->fetch_assoc();
            
            $conn->commit();
            
            return [
                'exito' => true,
                'id_pedido' => $id_pedido,
                'numero_dia' => $numero_dia,
                'fecha_hora' => $row_fecha['fecha_hora']
            ];
            
        } catch (\Exception $e) {
            $conn->rollback();
            return ['exito' => false, 'error' => $e->getMessage()];
        }
    }

    // Obtener el historial de pedidos de un usuario concreto.
    public static function listaPedidosUsuario($id_usuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha_hora DESC");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pedidos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = new Pedido(
                    $row['id'], $row['id_usuario'], $row['numero_dia'], 
                    $row['tipo'], $row['total_sin_descuento'], $row['descuento_aplicado'], $row['total_iva'], $row['estado'], $row['fecha_hora']
                );
            }
        }
        return $pedidos;
    }

    // Actualiza el estado de un pedido.
    public static function actualizaEstadoPedido($id_pedido, $nuevo_estado)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $id_pedido);
        return $stmt->execute();
    }

    //Obtiene pedidos filtrados por estados.
    public static function listaPedidosPorEstados($estados)
    {
        if (empty($estados)) return [];

        $conn = Aplicacion::getInstance()->getConexionBd();
        $placeholders = implode(',', array_fill(0, count($estados), '?'));
        $types = str_repeat('s', count($estados));
        
        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre_usuario, u.avatar
             FROM pedidos p
             JOIN usuarios u ON p.id_usuario = u.id
             WHERE p.estado IN ($placeholders)
             ORDER BY p.fecha_hora ASC"
        );
        $stmt->bind_param($types, ...$estados);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = new Pedido(
                    $row['id'], $row['id_usuario'], $row['numero_dia'], 
                    $row['tipo'], $row['total_sin_descuento'], $row['descuento_aplicado'], $row['total_iva'], $row['estado'], 
                    $row['fecha_hora'], $row['nombre_usuario'], $row['avatar']
                );
            }
        }
        return $pedidos;
    }

    // Obtiene todos los pedidos activos.
    public static function listaTodosLosPedidosActivos()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre_usuario, u.avatar
             FROM pedidos p
             JOIN usuarios u ON p.id_usuario = u.id
             WHERE p.estado NOT IN ('entregado', 'cancelado')
             ORDER BY p.fecha_hora ASC"
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = new Pedido(
                    $row['id'], $row['id_usuario'], $row['numero_dia'], 
                    $row['tipo'], $row['total_sin_descuento'], $row['descuento_aplicado'], $row['total_iva'], $row['estado'], 
                    $row['fecha_hora'], $row['nombre_usuario'], $row['avatar']
                );
            }
        }
        return $pedidos;
    }

    // Busca un pedido concreto por ID.
    public static function buscaPedido($id_pedido)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre_usuario, u.avatar
             FROM pedidos p
             JOIN usuarios u ON p.id_usuario = u.id
             WHERE p.id = ?"
        );
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return new Pedido(
                $row['id'], $row['id_usuario'], $row['numero_dia'], 
                $row['tipo'], $row['total_sin_descuento'], $row['descuento_aplicado'], $row['total_iva'], $row['estado'], 
                $row['fecha_hora'], $row['nombre_usuario'], $row['avatar']
            );
        }
        return false;
    }

    //Obtiene los productos (líneas) de un pedido concreto.
    public static function buscaDetallesPedido($id_pedido)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $sql = "SELECT pp.*, p.nombre 
                FROM pedido_productos pp 
                JOIN productos p ON pp.id_producto = p.id 
                WHERE pp.id_pedido = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $detalles = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $detalles[] = $row;
            }
        }
        return $detalles;
    }
}
?>