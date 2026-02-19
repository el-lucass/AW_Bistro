<?php
require_once __DIR__ . '/mysql/bd.php';

// 1. Busca usuario por ID
function buscaUsuario($id) {
    $conn = conectarBD();
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// 2. Busca usuario por Nombre (para login/registro)
function buscaUsuarioPorNombre($nombre) {
    $conn = conectarBD();
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// 3. Inserta nuevo usuario
function creaUsuario($user, $pass, $nom, $ape, $email, $rol = 'cliente') {
    $conn = conectarBD();
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, password, nombre, apellidos, email, rol) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $user, $hash, $nom, $ape, $email, $rol);
    return $stmt->execute();
}

// 4. Borra usuario
function borraUsuario($id) {
    $conn = conectarBD();
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// 5. Actualiza datos (usado en perfil y admin)
function actualizaUsuario($id, $nom, $ape, $email, $avatar, $rol) {
    $conn = conectarBD();
    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, email=?, avatar=?, rol=? WHERE id=?");
    $stmt->bind_param("sssssi", $nom, $ape, $email, $avatar, $rol, $id);
    return $stmt->execute();
}

// 6. Comprobar rol
function tieneRol($rolRequerido) {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === $rolRequerido;
}