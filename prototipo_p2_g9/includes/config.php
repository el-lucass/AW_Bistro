<?php
session_start();

// Configuración de la Base de Datos
define('BD_HOST', 'localhost');
define('BD_NAME', 'awp2');
define('BD_USER', 'awp2');
define('BD_PASS', 'awpass');

// Configuración de Rutas
define('RAIZ_APP', __DIR__);
// IMPORTANTE: Este nombre debe ser IGUAL al de tu carpeta en htdocs
define('RUTA_APP', '/Proyectos/AW_Bistro/prototipo_p2_g9');
define('RUTA_IMGS', RUTA_APP . '/img/');
define('RUTA_CSS', RUTA_APP . '/css/');
define('RUTA_JS', RUTA_APP . '/js/');

/**
 * Configuración del soporte de UTF-8, localización (idioma y país) y zona horaria
 */
ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');