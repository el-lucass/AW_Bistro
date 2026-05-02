<?php
namespace es\ucm\fdi\aw\recompensas;

use es\ucm\fdi\aw\Aplicacion;

class Recompensa
{
    private $id;
    private $id_producto;
    private $nombre_producto;
    private $bistrocoins;
    private $activa;

    private function __construct($id, $id_producto, $nombre_producto, $bistrocoins, $activa)
    {
        $this->id = $id;
        $this->id_producto = $id_producto;
        $this->nombre_producto = $nombre_producto;
        $this->bistrocoins = $bistrocoins;
        $this->activa = $activa;
    }

    public function getId() { return $this->id; }
    public function getIdProducto() { return $this->id_producto; }
    public function getNombreProducto() { return $this->nombre_producto; }
    public function getBistrocoins() { return $this->bistrocoins; }
    public function getActiva() { return $this->activa; }

    public static function creaRecompensa($id_producto, $bistrocoins)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("INSERT INTO recompensas (id_producto, bistrocoins, activa) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $id_producto, $bistrocoins);
        return $stmt->execute();
    }

    public static function listaRecompensas()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT r.*, p.nombre AS nombre_producto
                FROM recompensas r
                JOIN productos p ON r.id_producto = p.id
                ORDER BY r.id DESC";

        $result = $conn->query($sql);
        $recompensas = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recompensas[] = new Recompensa(
                    $row['id'],
                    $row['id_producto'],
                    $row['nombre_producto'],
                    $row['bistrocoins'],
                    $row['activa']
                );
            }
        }

        return $recompensas;
    }

    public static function listaRecompensasActivas()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT r.*, p.nombre AS nombre_producto
                FROM recompensas r
                JOIN productos p ON r.id_producto = p.id
                WHERE r.activa = 1
                ORDER BY r.bistrocoins ASC";

        $result = $conn->query($sql);
        $recompensas = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $recompensas[] = new Recompensa(
                    $row['id'],
                    $row['id_producto'],
                    $row['nombre_producto'],
                    $row['bistrocoins'],
                    $row['activa']
                );
            }
        }

        return $recompensas;
    }

    public static function existeRecompensaProducto($id_producto, $id_excluir = null){
        $conn = Aplicacion::getInstance()->getConexionBd();

        if ($id_excluir !== null) {
            $stmt = $conn->prepare("SELECT id FROM recompensas WHERE id_producto = ? AND id != ?");
            $stmt->bind_param("ii", $id_producto, $id_excluir);
        } else {
            $stmt = $conn->prepare("SELECT id FROM recompensas WHERE id_producto = ?");
            $stmt->bind_param("i", $id_producto);
        }

        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public static function buscaRecompensa($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $sql = "SELECT r.*, p.nombre AS nombre_producto
                FROM recompensas r
                JOIN productos p ON r.id_producto = p.id
                WHERE r.id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            return new Recompensa(
                $row['id'],
                $row['id_producto'],
                $row['nombre_producto'],
                $row['bistrocoins'],
                $row['activa']
            );
        }

        return false;
    }

    public static function actualizaRecompensa($id, $id_producto, $bistrocoins, $activa)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();

        $stmt = $conn->prepare(
            "UPDATE recompensas SET id_producto = ?, bistrocoins = ?, activa = ? WHERE id = ?"
        );

        $stmt->bind_param("iiii", $id_producto, $bistrocoins, $activa, $id);
        return $stmt->execute();
    }

    public static function borraRecompensa($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM recompensas WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}