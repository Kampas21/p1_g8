<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
    ?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Necesitas ser gerente para acceder a categorías.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

// IMPORTANTE: usar rutas con __DIR__
require_once __DIR__ . '/../../includes/categoriaService.php';

$categorias = CategoriaService::getAll();

$tituloPagina = 'Lista de Categorías';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Lista de Categorías</h1>

<p>
    <a href="categoria_form.php?modo=crear" class="btn primary">Nueva categoría</a>
</p>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>

    <?php foreach ($categorias as $cat): ?>
        <?php include __DIR__ . '/_fila_categoria.php'; ?>
    <?php endforeach; ?>

</table>

<p>
    <a class="btn-volver" href="../../index.php">Volver al inicio</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';