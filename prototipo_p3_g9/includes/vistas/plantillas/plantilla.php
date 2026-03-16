<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" type="text/css" href="<?= RUTA_CSS ?>estilo.css">
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