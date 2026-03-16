<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Aplicacion;

class Usuario
{
    // 1. Propiedades privadas
    private $id;
    private $nombreUsuario;
    private $password;
    private $nombre;
    private $apellidos;
    private $email;
    private $rol;
    private $avatar;

    // 2. Constructor privado (Solo se crea a través de métodos de la propia clase)
    private function __construct($id, $nombreUsuario, $password, $nombre, $apellidos, $email, $rol, $avatar)
    {
        $this->id = $id;
        $this->nombreUsuario = $nombreUsuario;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->rol = $rol;
        $this->avatar = $avatar;
    }

    // 3. Getters (Para poder acceder a los datos del objeto desde fuera)
    public function getId() { return $this->id; }
    public function getNombreUsuario() { return $this->nombreUsuario; }
    public function getPassword() { return $this->password; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getEmail() { return $this->email; }
    public function getRol() { return $this->rol; }
    public function getAvatar() { return $this->avatar; }

    // =========================================================
    // MÉTODOS ESTÁTICOS (Interacciones con la Base de Datos)
    // =========================================================

    /**
     * Comprueba si la contraseña es correcta para un usuario.
     */
    public static function login($nombreUsuario, $password)
    {
        $usuario = self::buscaUsuarioPorNombre($nombreUsuario);
        if ($usuario && password_verify($password, $usuario->getPassword())) {
            return $usuario;
        }
        return false;
    }

    /**
     * Busca un usuario por su ID.
     */
    public static function buscaUsuario($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            return new Usuario(
                $fila['id'], $fila['nombre_usuario'], $fila['password'], 
                $fila['nombre'], $fila['apellidos'], $fila['email'], 
                $fila['rol'], $fila['avatar']
            );
        }
        return false;
    }

    /**
     * Busca un usuario por su nombre de usuario.
     */
    public static function buscaUsuarioPorNombre($nombreUsuario)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $stmt->bind_param("s", $nombreUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            $fila = $resultado->fetch_assoc();
            return new Usuario(
                $fila['id'], $fila['nombre_usuario'], $fila['password'], 
                $fila['nombre'], $fila['apellidos'], $fila['email'], 
                $fila['rol'], $fila['avatar']
            );
        }
        return false;
    }

    /**
     * Inserta un nuevo usuario en la base de datos.
     */
    public static function creaUsuario($user, $pass, $nom, $ape, $email, $rol = 'cliente')
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        // Asignamos 'default.png' por defecto en el registro
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, password, nombre, apellidos, email, rol, avatar) VALUES (?, ?, ?, ?, ?, ?, 'default.png')");
        $stmt->bind_param("ssssss", $user, $hash, $nom, $ape, $email, $rol);
        
        return $stmt->execute();
    }

    /**
     * Actualiza los datos de un usuario existente.
     */
    public static function actualizaUsuario($id, $nom, $ape, $email, $avatar, $rol)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, email=?, avatar=?, rol=? WHERE id=?");
        $stmt->bind_param("sssssi", $nom, $ape, $email, $avatar, $rol, $id);
        
        return $stmt->execute();
    }

    /**
     * Borra un usuario de la base de datos.
     */
    public static function borraUsuario($id)
    {
        $conn = Aplicacion::getInstance()->getConexionBd();
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }

    /**
     * Utilidad estática para comprobar roles desde las vistas.
     */
    public static function tieneRol($rolRequerido)
    {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === $rolRequerido;
    }
}