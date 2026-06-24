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


public static function valoracionesDelProducto($id_producto){
        $conn = Aplicacion::getInstance()->getConexionBd();        
        $stmt = $conn->prepare("SELECT * FROM valoraciones WHERE id_producto = ?");
        $stmt->bind_param("i",  $id_producto);

        $stmt->execute(); // 1. Primero ejecutamos
        return $stmt->get_result(); // 2. Luego devolvemos el resultado
    }

    public static function resumenValoracionesProducto($id_producto){
        $valoraciones = self::valoracionesDelProducto($id_producto);
        
        $resumen = []; // Es buena práctica inicializar el array
        $resumen['num_valoraciones'] = $valoraciones->num_rows;
        $resumen['media'] = 0; // Por defecto la media es 0

        if($resumen['num_valoraciones'] > 0) {
            $suma_puntuaciones = 0; // Creamos una variable para ir sumando
            
            // ¡Corregido! Usamos $valoraciones, no $result
            while ($row = $valoraciones->fetch_assoc()) {
                $suma_puntuaciones += $row['puntuacion']; // Sumamos todas las notas
            }
            
            // La media se calcula FUERA del bucle, dividiendo la suma total
            // entre el número total de valoraciones que ya teníamos guardado.
            $resumen['media'] = $suma_puntuaciones / $resumen['num_valoraciones'];
        }
        
        return $resumen;
    }

}
