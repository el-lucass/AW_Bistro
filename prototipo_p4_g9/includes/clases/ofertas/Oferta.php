<?php
namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Aplicacion;

class Oferta
{
    // 1. Propiedades privadas de la Oferta
    private $id;
    private $nombre;
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $porcentaje_descuento;
    private $productos; // Array con los productos y cantidades requeridas para esta oferta

    // 2. Constructor privado
    private function __construct($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $porcentaje_descuento, $productos = [])
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->porcentaje_descuento = $porcentaje_descuento;
        $this->productos = $productos;
    }

    // 3. GETTERS
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getFechaInicio() { return $this->fecha_inicio; }
    public function getFechaFin() { return $this->fecha_fin; }
    public function getPorcentajeDescuento() { return $this->porcentaje_descuento; }
    public function getProductos() { return $this->productos; }


    // Crear una nueva oferta
    public static function creaOferta($nombre, $descripcion, $fecha_inicio, $fecha_fin, $porcentaje_descuento, $productos)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $conn->begin_transaction();

        try {
            //Insertar datos principales de la oferta
            $stmt = $conn->prepare("INSERT INTO ofertas (nombre, descripcion, fecha_inicio, fecha_fin, porcentaje_descuento) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssd", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $porcentaje_descuento);
            $stmt->execute();
            
            $id_oferta = $conn->insert_id;

            //Insertar los productos necesarios para que la oferta sea aplicable
            $stmt_prod = $conn->prepare("INSERT INTO oferta_productos (id_oferta, id_producto, cantidad_requerida) VALUES (?, ?, ?)");
            
            foreach ($productos as $item) {
                $id_producto = $item['id_producto'];
                $cantidad = $item['cantidad'];
                $stmt_prod->bind_param("iii", $id_oferta, $id_producto, $cantidad);
                $stmt_prod->execute();
            }

            $conn->commit();
            return $id_oferta;

        } catch (\Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    //Listar todas las ofertas (para el Gerente: actuales y pasadas). 
    public static function listaTodasLasOfertas()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM ofertas ORDER BY fecha_inicio DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        $ofertas = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $productos = self::buscaProductosOferta($row['id']);
                $ofertas[] = new Oferta(
                    $row['id'], $row['nombre'], $row['descripcion'], 
                    $row['fecha_inicio'], $row['fecha_fin'], $row['porcentaje_descuento'], $productos
                );
            }
        }
        return $ofertas;
    }

  
    //Listar solo ofertas activas actualmente (útil para la vista del Cliente).
    public static function listaOfertasActivas()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM ofertas WHERE fecha_inicio <= NOW() AND fecha_fin >= NOW() ORDER BY fecha_fin ASC");
        $stmt->execute();
        $result = $stmt->get_result();

        $ofertas = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $productos = self::buscaProductosOferta($row['id']);
                $ofertas[] = new Oferta(
                    $row['id'], $row['nombre'], $row['descripcion'], 
                    $row['fecha_inicio'], $row['fecha_fin'], $row['porcentaje_descuento'], $productos
                );
            }
        }
        return $ofertas;
    }

    //4. Buscar una oferta concreta por ID (y consultar sus detalles).
    public static function buscaOferta($id_oferta)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id_oferta);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $productos = self::buscaProductosOferta($row['id']);
            return new Oferta(
                $row['id'], $row['nombre'], $row['descripcion'], 
                $row['fecha_inicio'], $row['fecha_fin'], $row['porcentaje_descuento'], $productos
            );
        }
        return false;
    }

    //Actualizar una oferta existente.
    public static function actualizaOferta($id_oferta, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $porcentaje_descuento, $productos)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $conn->begin_transaction();

        try {
            // Actualizar datos principales
            $stmt = $conn->prepare("UPDATE ofertas SET nombre=?, descripcion=?, fecha_inicio=?, fecha_fin=?, porcentaje_descuento=? WHERE id=?");
            $stmt->bind_param("ssssdi", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $porcentaje_descuento, $id_oferta);
            $stmt->execute();

            // Para actualizar los productos requeridos, lo más seguro es borrarlos y volver a insertarlos
            $stmt_del = $conn->prepare("DELETE FROM oferta_productos WHERE id_oferta=?");
            $stmt_del->bind_param("i", $id_oferta);
            $stmt_del->execute();

            $stmt_prod = $conn->prepare("INSERT INTO oferta_productos (id_oferta, id_producto, cantidad_requerida) VALUES (?, ?, ?)");
            foreach ($productos as $item) {
                $id_producto = $item['id_producto'];
                $cantidad = $item['cantidad'];
                $stmt_prod->bind_param("iii", $id_oferta, $id_producto, $cantidad);
                $stmt_prod->execute();
            }

            $conn->commit();
            return true;

        } catch (\Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    //Borrar una oferta
    public static function borraOferta($id_oferta)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id_oferta);
        return $stmt->execute();
    }


    //Método auxiliar privado: Obtiene los productos que requiere una oferta.
    private static function buscaProductosOferta($id_oferta)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT op.id_producto, op.cantidad_requerida, p.nombre, p.precio_base 
                FROM oferta_productos op 
                JOIN productos p ON op.id_producto = p.id 
                WHERE op.id_oferta = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_oferta);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }
        return $productos;
    }


    public static function aplicarOfertasAlCarrito($productosCarrito) {
        $ofertasActivas = self::listaOfertasActivas();
        $descuentosAplicados = [];
        $totalDescuento = 0;

        // Array con las cantidades disponibles en el carrito para ir "gastándolas"
        // otro array con los precios para calcular el valor del descuento
        $cantidadesDisponibles = [];
        $preciosCarrito = [];
        foreach ($productosCarrito as $item) {
            $cantidadesDisponibles[$item['id_producto']] = $item['cantidad'];
            $preciosCarrito[$item['id_producto']] = $item['precio'];
        }

        // Comprobamos cada oferta
        foreach ($ofertasActivas as $oferta) {
            $productosRequeridos = $oferta->getProductos();
            $vecesAplicable = 999999; 
            $precioBasePack = 0;

            // Comprobamos si tenemos suficientes productos para esta oferta
            foreach ($productosRequeridos as $req) {
                $idReq = $req['id_producto'] ?? $req['id']; 
                $cantReq = $req['cantidad_requerida'] ?? $req['cantidad'];

                // Si no está en el carrito o no hay suficientes, esta oferta no se aplica (0 veces)
                if (!isset($cantidadesDisponibles[$idReq]) || $cantidadesDisponibles[$idReq] < $cantReq) {
                    $vecesAplicable = 0;
                    break;
                }

                // Vemos para cuántos packs nos da de este producto en concreto
                $vecesPosibles = floor($cantidadesDisponibles[$idReq] / $cantReq);
                if ($vecesPosibles < $vecesAplicable) {
                    $vecesAplicable = $vecesPosibles;
                }

                // Sumamos el precio base de este producto al total del pack
                $precioBasePack += ($preciosCarrito[$idReq] ?? 0) * $cantReq;
            }

            // Si la oferta se puede aplicar al menos 1 vez
            if ($vecesAplicable > 0) {
                // "Gastamos" los productos restándolos del inventario temporal
                foreach ($productosRequeridos as $req) {
                    $idReq = $req['id_producto'] ?? $req['id'];
                    $cantReq = $req['cantidad_requerida'] ?? $req['cantidad'];
                    $cantidadesDisponibles[$idReq] -= ($cantReq * $vecesAplicable);
                }

                // Calculamos el descuento monetario
                $porcentaje = $oferta->getPorcentajeDescuento();
                $descuentoMonetarioPorPack = $precioBasePack * ($porcentaje / 100);
                $ahorroTotalOferta = $descuentoMonetarioPorPack * $vecesAplicable;

                $totalDescuento += $ahorroTotalOferta;
                $descuentosAplicados[] = [
                    'nombre' => $oferta->getNombre(),
                    'veces' => $vecesAplicable,
                    'ahorro' => $ahorroTotalOferta
                ];
            }
        }

        return [
            'detalles' => $descuentosAplicados, // Array con nombres y ahorro de cada oferta aplicada
            'total_descuento' => $totalDescuento  // Float con el total a restar
        ];
    }
}