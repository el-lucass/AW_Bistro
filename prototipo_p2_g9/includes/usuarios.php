<?php
// Función para verificar el login
function login($usuario, $password) {
    $conn = conectarBD(); // Función que deberías tener en tu config o bd.php
    $sql = "SELECT * FROM Usuarios WHERE nombre_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Verificamos la contraseña (asumiendo que usamos password_hash)
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

// Función para comprobar si el usuario tiene un rol específico
function tieneRol($rolRequerido) {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === $rolRequerido;
}
?>