<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

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

// 🔥 IMPORTANTE
$categorias = CategoriaService::getAll();

$tituloPagina = 'Categorías';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Lista de categorías</h1>

<p>
    <a href="categoria_form.php?modo=crear" class="btn-nuevo">
        + Nueva categoría
    </a>
</p>

<?php if (empty($categorias)): ?>
    <p>No hay categorías.</p>
<?php else: ?>

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

        <td><?= htmlspecialchars($cat->getDescripcion()) ?></td>

        <td>
            <?= $cat->isActiva()
                ? '<span class="text-success">Activa</span>'
                : '<span class="text-danger">Inactiva</span>' ?>
        </td>

        <td>

            <a href="categoria_form.php?id=<?= $cat->getId() ?>">
                Editar
            </a>

            <a href="../productos/mostrarProductosCategoria.php?id=<?= $cat->getId() ?>">
                Productos
            </a>

            <?php if ($cat->isActiva()): ?>
                <form method="POST" action="borrarCategoria.php" class="form-inline-action">
                    <input type="hidden" name="id" value="<?= $cat->getId() ?>">
                    <button type="submit" onclick="return confirm('¿Desactivar?')">
                        Desactivar
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="activarCategoria.php" class="form-inline-action">
                    <input type="hidden" name="id" value="<?= $cat->getId() ?>">
                    <button type="submit">
                        Activar
                    </button>
                </form>
            <?php endif; ?>

        </td>
    </tr>

    <?php endforeach; ?>

</table>

<?php endif; ?>

<p>
    <a href="../../index.php" class="btn-volver">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';