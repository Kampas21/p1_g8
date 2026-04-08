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
    <a class="btn-nuevo" href="crearCategoria.php">Nueva Categoría</a>
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
    <tr>
        <td><?= $cat->getId() ?></td>

        <td><?= htmlspecialchars($cat->getNombre()) ?></td>

        <td class="descripcion">
            <?= htmlspecialchars($cat->getDescripcion()) ?>
        </td>

        <td>
            <?= $cat->isActiva()
                ? '<span style="color:green">Activa</span>'
                : '<span style="color:red">Inactiva</span>' ?>
        </td>

        <td>
            <a class="btn editar"
               href="editarCategoria.php?id=<?= $cat->getId() ?>">
                Editar
            </a>

            <a class="btn prod"
               href="../productos/mostrarProductosCategoria.php?id=<?= $cat->getId() ?>">
                Productos
            </a>

            <?php if ($cat->isActiva()): ?>
                <a class="btn activar"
                   href="borrarCategoria.php?id=<?= $cat->getId() ?>"
                   onclick="return confirm('¿Seguro que quieres desactivar esta categoría?')">
                    Desactivar
                </a>
            <?php else: ?>
                <a class="btn borrar"
                   href="activarCategoria.php?id=<?= $cat->getId() ?>">
                    Activar
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<p>
    <a class="btn-volver" href="../../index.php">Volver al inicio</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';