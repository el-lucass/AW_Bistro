<?php
require_once '../includes/config.php';
require_once '../includes/mysql/bd.php';

// 1. Seguridad: Solo el gerente puede crear usuarios aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Recoger datos del formulario de admin_crear_usuario.php
    $usuario = $_POST['nombre_usuario'] ?? null;
    $password = $_POST['password'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $apellidos = $_POST['apellidos'] ?? null;
    $email = $_POST['email'] ?? null;
    $rol = $_POST['rol'] ?? 'cliente'; // Recogemos el rol del select

    if ($usuario && $password && $email) {
        $conn = conectarBD();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // 3. Insertar incluyendo el campo ROL que el gerente ha elegido
            $sql = "INSERT INTO usuarios (nombre_usuario, password, nombre, apellidos, email, rol) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $usuario, $hash, $nombre, $apellidos, $email, $rol);

            if ($stmt->execute()) {
                // Éxito: Volvemos a la lista para ver el nuevo usuario
                header('Location: ../admin/usuarios.php?msg=usuario_creado');
                exit;
            } else {
                echo "Error al ejecutar la inserción: " . $stmt->error;
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "Error: El nombre de usuario o el email ya existen.";
            } else {
                echo "Error de base de datos: " . $e->getMessage();
            }
        }
    } else {
        echo "Error: Faltan campos obligatorios (usuario, contraseña o email).";
    }
} else {
    header('Location: ../admin/crear_usuario.php');
    exit;
}