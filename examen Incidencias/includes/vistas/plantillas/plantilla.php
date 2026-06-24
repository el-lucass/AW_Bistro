<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?>estilo.css">
    <script defer src="<?= RUTA_JS ?>validaciones.js"></script>
</head>
<body>
    <div id="contenedor">
        <?php require RAIZ_APP . '/vistas/comun/cabecera.php'; ?>
        <?php require RAIZ_APP . '/vistas/comun/sideBarIzq.php'; ?>
        
        <main>
            <article>
                <?= $contenidoPrincipal ?>
            </article>
        </main>
        
        <?php require RAIZ_APP . '/vistas/comun/sideBarDer.php'; ?>
        <?php require RAIZ_APP . '/vistas/comun/pie.php'; ?>
    </div>
</body>
</html>