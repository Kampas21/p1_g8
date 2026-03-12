<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) : 'Bistro FDI' ?></title>
    <link rel="stylesheet" type="text/css" href="<?= $rutaCSS ?? 'CSS/estilo.css' ?>">
</head>
<body>

<div id="contenedor-web">

    <header id="cabecera-web">
        <?php require __DIR__ . '/../cabecera.php'; ?>
    </header>

    <div id="zona-central">
        <aside id="sidebar-izq">
            <?php require __DIR__ . '/../sideBarIzq.php'; ?>
        </aside>

        <main id="contenido-web">
            <?= $contenidoPrincipal ?? '' ?>
        </main>

        <aside id="sidebar-der">
            <?php require __DIR__ . '/../sideBarDer.php'; ?>
        </aside>
    </div>

    <footer id="pie-web">
        <?php require __DIR__ . '/../pie.php'; ?>
    </footer>
</div>

</body>
</html>