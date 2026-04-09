<?php declare(strict_types=1); ?>
<tr>
    <td><?= $cat->getId() ?></td>
    <td><?= htmlspecialchars($cat->getNombre()) ?></td>
    <td class="descripcion"><?= htmlspecialchars($cat->getDescripcion()) ?></td>
    <td>
        <?= $cat->isActiva() ? '<span class="texto-exito">Activa</span>' : '<span class="texto-error">Inactiva</span>' ?>
    </td>
    <td>
        <div class="actions-inline">
            <a href="categoria_form.php?id=<?= $cat->getId() ?>" class="btn small primary">Editar</a>
            <a class="btn small prod" href="../productos/mostrarProductosCategoria.php?id=<?= $cat->getId() ?>">Productos</a>

            <?php if ($cat->isActiva()): ?>
                <form method="post" action="borrarCategoria.php" onsubmit="return confirm('¿Desactivar?');" class="d-inline">
                    <input type="hidden" name="id" value="<?= $cat->getId() ?>">
                    <button class="btn small danger" type="submit">Desactivar</button>
                </form>
            <?php else: ?>
                <form method="post" action="activarCategoria.php" class="d-inline">
                    <input type="hidden" name="id" value="<?= $cat->getId() ?>">
                    <button class="btn small" type="submit">Activar</button>
                </form>
            <?php endif; ?>
        </div>
    </td>
</tr>