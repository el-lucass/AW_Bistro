<?php
namespace es\ucm\fdi\aw\pedidos;

use es\ucm\fdi\aw\Aplicacion;

class Valoracion
{
    // 1. Propiedades privadas
    private $id_usuario;
    private $id_producto;
    private $puntuacion;
    private $comentario;

    // 2. Constructor privado (Solo se crea a través de métodos de la propia clase)
    private function __construct($id_usuario, $id_producto, $puntuacion, $comentario)
    {
        $this->id_usuario = $id_usuario;
        $this->id_producto = $id_producto;
        $this->puntuacion = $puntuacion;
        $this->comentario = $comentario;
    }

    // 3. Getters (Para poder acceder a los datos del objeto desde fuera)
    public function getIdUsuario() { return $this->id_usuario; }
    public function getIdProducto() { return $this->id_producto; }
    public function getPuntuacion() { return $this->puntuacion; }
    public function getComentario() { return $this->comentario; }


    public static function borrarValoracion($id_usuario, $id_producto){

        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM valoraciones WHERE id_usuario = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_usuario, $id_producto);
        
        return $stmt->execute();
    }

    public static function actualizaValoracion($id_usuario, $id_producto, $puntuacion, $comentario){

        self::borrarValoracion($id_usuario, $id_producto);

        $conn = Aplicacion::getInstance()->getConexionBd();        
        $stmt = $conn->prepare("INSERT INTO valoraciones (id_usuario, id_producto, puntuacion, comentario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $id_usuario, $id_producto, $puntuacion, $comentario);
        
        return $stmt->execute();
    }

}
