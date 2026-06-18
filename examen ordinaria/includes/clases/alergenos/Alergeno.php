<?php
namespace es\ucm\fdi\aw\alergenos;

use es\ucm\fdi\aw\Aplicacion;

class Alergeno
{
    // Propiedades privadas del Alergeno
    private $id;
    private $nombre;
    private $imagen_grande;
    private $imagen_pequena;

    //Constructor privado 
    private function __construct($id, $nombre, $imagen_grande, $imagen_pequena)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->imagen_grande = $imagen_grande;
        $this->imagen_pequena = $imagen_pequena;

    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getImagenGrande() { return $this->imagen_grande; }
    public function getImagenPequena() { return $this->imagen_pequena; }



    public static function listaAlergenos() {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT * FROM alergenos";
        $result = $conn->query($sql);
        
        $alergenos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $alergenos[] = $row;
            }
        }
        return $alergenos;
    }

    public static function alergeno_producto($id_alergeno, $id_producto){
     
        $conn = Aplicacion::getInstance()->getConexionBd();     
        $stmt = $conn->prepare("SELECT * FROM alergeno_productos WHERE id_alergeno = ? AND id_producto = ?");
        $stmt->bind_param("ii", $id_alergeno, $id_producto);
        $stmt->execute();
        $result = $stmt->get_result();

        $res = 0;
        if($result->num_rows > 0){
            $res = 1;
        }

        return $res;
    }

    public static function borrarAlergenos($id_producto){      
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM alergeno_productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id_producto);
        $stmt->execute();
    }

    public static function ponerAlergeno($id_producto, $id_alergeno){
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $stmt = $conn->prepare("INSERT INTO alergeno_productos (id_alergeno, id_producto) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_alergeno, $id_producto);
        $stmt->execute();
    }
}
?>