<?php
/**
 * Función para autocargar clases
 */
spl_autoload_register(function ($class) {
    $prefix = 'es\\ucm\\fdi\\aw\\';
    $base_dir = __DIR__ . '/clases/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Importamos la clase Aplicacion (que ahora sí tiene su namespace)
use es\ucm\fdi\aw\Aplicacion;

/**
 * Parámetros de conexión a la BD
 */
define('BD_HOST', 'localhost');
define('BD_NAME', 'awp3');
define('BD_USER', 'awp3'); // Recuerda usar 'root' si te daba Access Denied
define('BD_PASS', 'awpass'); // Vacío '' si usas 'root' en XAMPP

// Configuración de Rutas
define('RAIZ_APP', __DIR__);
define('RUTA_APP', '/Proyectos/AW_Bistro/prototipo_p3_g9');
define('RUTA_IMGS', RUTA_APP . '/img/');
define('RUTA_CSS', RUTA_APP . '/CSS/');
define('RUTA_JS', RUTA_APP . '/JS/');

ini_set('default_charset', 'UTF-8');
setLocale(LC_ALL, 'es_ES.UTF.8');
date_default_timezone_set('Europe/Madrid');

// Inicializa la aplicación (¡Sin barra invertida porque hemos usado 'use' arriba!)
$app = Aplicacion::getInstance();
$app->init(array('host'=>BD_HOST, 'bd'=>BD_NAME, 'user'=>BD_USER, 'pass'=>BD_PASS), RUTA_APP, RAIZ_APP);

register_shutdown_function([$app, 'shutdown']);

require_once __DIR__ . '/vistas/helpers/utils.php';