<?php
require_once 'includes/config.php';

// Cerramos la sesión
session_unset();
session_destroy();

// Redirigimos usando la nueva ruta absoluta corregida
header('Location: ' . RUTA_APP . '/index.php');
exit;