<?php
namespace es\ucm\fdi\aw\incidencias;

use es\ucm\fdi\aw\Aplicacion;

class Incidencia
{
    // 1. Propiedades privadas de la Incidencia
    private $id_usuario;
    private $id_pedido;
    private $causas;
    private $descripcion;
    private $imagen;
    private $estado;

    // 2. Constructor privado
    private function __construct($id_usuario, $id_pedido, $causas, $descripcion, $imagen, $estado)
    {
        $this->id_usuario = $id_usuario;
        $this->id_pedido = $id_pedido;
        $this->causas = $causas;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
        $this->estado = $estado;
    }

    // 3. GETTERS
    public function getIdUsuario() { return $this->id_usuario; }
    public function getIdPedido() { return $this->id_pedido; }
    public function getCausas() { return $this->causas; }
    public function getDescripcion() { return $this->descripcion; }
    public function getImagen() { return $this->imagen; }
    public function getEstado() { return $this->estado; }

    public static function getEstadoPorIdPedido($id_pedido){

        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM incidencias WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            return $fila['estado'];
        }

        return false;
    }

    public static function existeIncidencia($id_pedido){

        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM incidencias WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            return true;
        }

        return false;
    }


    public static function buscaIncidencia($id_pedido)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM incidencias WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            return new Incidencia(
                $fila['id_usuario'], $fila['id_pedido'], $fila['causas'], 
                $fila['descripcion'], $fila['imagen'], $fila['estado']
            );
        }
        return false;
    }

    public static function opcionesDeCausa(){
        $causas = ["retraso" , "falta producto" , "mal estado", "otro"];
        return $causas;        
    }


    public static function ponerIncidencia($id_usuario, $id_pedido, $causas, $descripcion, $imagen, $estado){
        if($imagen == "") $imagen = "default.png";
        if($estado == "") $estado = "pendiente";
        
        self::borraIncidencia($id_pedido);
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        // Asignamos 'default.png' por defecto en el registro
        $stmt = $conn->prepare("INSERT INTO incidencias (id_usuario, id_pedido, causas, descripcion, imagen, estado) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $id_usuario, $id_pedido, $causas, $descripcion, $imagen, $estado);
        
        return $stmt->execute();
    }


    public static function borraIncidencia($id_pedido)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM incidencias WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        
        return $stmt->execute();
    }

    public static function listaIncidencias() {

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT * FROM incidencias";
        $result = $conn->query($sql);
        
        $incidencias = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $incidencias[] = $row;
            }
        }
        return $incidencias;
    }

    public static function cambiarEstado($id_pedido, $estado){

        $nuevo_estado = $estado == "pendiente" ? 'resuelta' : 'pendiente';

        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "UPDATE incidencias SET estado = ? WHERE id_pedido = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_pedido);
        
        return $stmt->execute();

    }

}