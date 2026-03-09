<?php
namespace es\ucm\fdi\aw;

class Producto
{
    // 1. Propiedades privadas del Producto
    private $id;
    private $nombre;
    private $descripcion;
    private $id_categoria;
    private $nombre_categoria; // Obtenido con el JOIN
    private $precio_base;
    private $iva;
    private $disponible;
    private $ofertado;
    private $imagen_principal; // Obtenido con la subconsulta
    private $imagenes; // Array con todas las fotos

    // 2. Constructor privado
    private function __construct($id, $nombre, $descripcion, $id_categoria, $nombre_categoria, $precio_base, $iva, $disponible, $ofertado, $imagen_principal, $imagenes = [])
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->id_categoria = $id_categoria;
        $this->nombre_categoria = $nombre_categoria;
        $this->precio_base = $precio_base;
        $this->iva = $iva;
        $this->disponible = $disponible;
        $this->ofertado = $ofertado;
        $this->imagen_principal = $imagen_principal;
        $this->imagenes = $imagenes;
    }

    // 3. GETTERS (Para leer los datos desde la vista)
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getIdCategoria() { return $this->id_categoria; }
    public function getNombreCategoria() { return $this->nombre_categoria; }
    public function getPrecioBase() { return $this->precio_base; }
    public function getIva() { return $this->iva; }
    public function getDisponible() { return $this->disponible; }
    public function getOfertado() { return $this->ofertado; }
    public function getImagenPrincipal() { return $this->imagen_principal; }
    public function getImagenes() { return $this->imagenes; }
    
    // Getter calculado súper útil para la vista
    public function getPrecioTotal() { 
        return $this->precio_base + ($this->precio_base * ($this->iva / 100)); 
    }

    // =========================================================
    // MÉTODOS ESTÁTICOS PARA PRODUCTOS
    // =========================================================

    public static function listaProductos($solo_ofertados = true)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $sql = "SELECT p.*, c.nombre AS nombre_categoria, 
                       (SELECT ruta_imagen FROM producto_imagenes pi WHERE pi.id_producto = p.id ORDER BY id ASC LIMIT 1) AS imagen_principal
                FROM productos p
                JOIN categorias c ON p.id_categoria = c.id";
                
        if ($solo_ofertados) {
            $sql .= " WHERE p.ofertado = TRUE";
        }
        
        $result = $conn->query($sql);
        $productos = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Creamos un objeto Producto por cada fila
                $productos[] = new Producto(
                    $row['id'], $row['nombre'], $row['descripcion'], $row['id_categoria'], 
                    $row['nombre_categoria'], $row['precio_base'], $row['iva'], 
                    $row['disponible'], $row['ofertado'], $row['imagen_principal']
                );
            }
        }
        return $productos;
    }

    public static function buscaProducto($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        
        $stmt = $conn->prepare("SELECT p.*, c.nombre AS nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        
        if ($row) {
            // Buscamos sus imágenes
            $stmt_img = $conn->prepare("SELECT id, ruta_imagen FROM producto_imagenes WHERE id_producto = ? ORDER BY id ASC");
            $stmt_img->bind_param("i", $id);
            $stmt_img->execute();
            $result_img = $stmt_img->get_result();
            
            $imagenes = [];
            while ($img = $result_img->fetch_assoc()) {
                $imagenes[] = $img;
            }
            
            return new Producto(
                $row['id'], $row['nombre'], $row['descripcion'], $row['id_categoria'], 
                $row['nombre_categoria'], $row['precio_base'], $row['iva'], 
                $row['disponible'], $row['ofertado'], $imagenes[0]['ruta_imagen'] ?? null, $imagenes
            );
        }
        return false;
    }

    public static function creaProducto($nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes = [])
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $conn->begin_transaction(); 
        
        try {
            $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, id_categoria, precio_base, iva, disponible, ofertado) VALUES (?, ?, ?, ?, ?, ?, TRUE)");
            $stmt->bind_param("ssidii", $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible);
            $stmt->execute();
            
            $id_producto = $conn->insert_id;
            
            if (!empty($rutas_imagenes)) {
                $stmt_img = $conn->prepare("INSERT INTO producto_imagenes (id_producto, ruta_imagen) VALUES (?, ?)");
                foreach ($rutas_imagenes as $ruta) {
                    $stmt_img->bind_param("is", $id_producto, $ruta);
                    $stmt_img->execute();
                }
            }
            
            $conn->commit();
            return true;
        } catch (\Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public static function actualizaProducto($id, $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $rutas_imagenes = [])
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $conn->begin_transaction(); 
        
        try {
            $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, id_categoria=?, precio_base=?, iva=?, disponible=? WHERE id=?");
            $stmt->bind_param("ssidiii", $nombre, $descripcion, $id_categoria, $precio_base, $iva, $disponible, $id);
            $stmt->execute();
            
            if (!empty($rutas_imagenes)) {
                $stmt_img = $conn->prepare("INSERT INTO producto_imagenes (id_producto, ruta_imagen) VALUES (?, ?)");
                foreach ($rutas_imagenes as $ruta) {
                    $stmt_img->bind_param("is", $id, $ruta);
                    $stmt_img->execute();
                }
            }
            
            $conn->commit();
            return true;
        } catch (\Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public static function borraProducto($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("UPDATE productos SET ofertado = FALSE WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function restauraProducto($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("UPDATE productos SET ofertado = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // =========================================================
    // MÉTODOS ESTÁTICOS PARA CATEGORÍAS E IMÁGENES
    // =========================================================
    // Nota: Devolvemos arrays asociativos simples para las categorías 
    // para no tener que crear otra clase entera ahora mismo.

    public static function listaCategorias()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $result = $conn->query("SELECT * FROM categorias");
        
        $categorias = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
        return $categorias;
    }

    public static function listaCategoriasConConteo()
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $sql = "SELECT c.*, COUNT(p.id) AS total_productos 
                FROM categorias c 
                LEFT JOIN productos p ON c.id = p.id_categoria 
                GROUP BY c.id";
        $result = $conn->query($sql);
        
        $categorias = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
        }
        return $categorias;
    }

    public static function buscaCategoria($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function creaCategoria($nombre, $descripcion)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        return $stmt->execute();
    }

    public static function actualizaCategoria($id, $nombre, $descripcion)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        return $stmt->execute();
    }

    public static function borraCategoria($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public static function buscaImagen($id_imagen)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM producto_imagenes WHERE id = ?");
        $stmt->bind_param("i", $id_imagen);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function borraImagen($id_imagen)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM producto_imagenes WHERE id = ?");
        $stmt->bind_param("i", $id_imagen);
        return $stmt->execute();
    }
}