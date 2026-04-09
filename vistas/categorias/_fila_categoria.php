<?php declare(strict_types=1); ?>
<tr>
    <td><?= (int)$cat->getId() ?></td>
    <td><?= htmlspecialchars($cat->getNombre(), ENT_QUOTES, 'UTF-8') ?></td>
    <td class="descripcion"><?= htmlspecialchars($cat->getDescripcion(), ENT_QUOTES, 'UTF-8') ?></td>
    <td>
        <?php if ($cat->isActiva()): ?>
            <span class="texto-exito">Activa</span>
        <?php else: ?>
            <span class="texto-error">Inactiva</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="actions-inline">
            <a href="editarCategoria.php?id=<?= (int)$cat->getId() ?>" class="btn small primary">Editar</a>
            <a href="../productos/mostrarProductosCategoria.php?id=<?= (int)$cat->getId() ?>" class="btn small prod">Productos</a>

            <?php if ($cat->isActiva()): ?>
                <form method="post" action="borrarCategoria.php" onsubmit="return confirm('¿Desactivar categoría?');" class="d-inline">
                    <input type="hidden" name="id" value="<?= (int)$cat->getId() ?>">
                    <button class="btn small danger" type="submit">Desactivar</button>
                </form>
            <?php else: ?>
                <form method="post" action="activarCategoria.php" class="d-inline">
                    <input type="hidden" name="id" value="<?= (int)$cat->getId() ?>">
                    <button class="btn small" type="submit">Activar</button>
                </form>
            <?php endif; ?>
        </div>
    </td>
</tr>