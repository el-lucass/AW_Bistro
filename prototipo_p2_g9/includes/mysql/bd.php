<?php
require_once __DIR__ . '/../config.php';

function conectarBD() {
    $conn = new mysqli(BD_HOST, BD_USER, BD_PASS, BD_NAME);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}