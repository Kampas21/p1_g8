<?php


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/CategoriaDAO.php';

$categorias = CategoriaDAO::getAll();

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
    ?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Necesitas ser gerente para acceder a categorías.</p>
        <a class="btn-volver" href="../../index.php">Volver</a>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

$tituloPagina = 'Categorías';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Lista de categorías</h1>

<p>
    <a href="crearCategoria.php" class="btn-nuevo">+ Nueva categoría</a>
</p>

<?php if (empty($categorias)): ?>
    <p>No hay categorías.</p>
<?php else: ?>
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categorias as $cat): ?>
                <?php require __DIR__ . '/_fila_categoria.php'; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p>
    <a href="../../index.php" class="btn-volver">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';