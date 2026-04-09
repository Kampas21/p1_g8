<?php declare(strict_types=1); ?>
<article class="producto-card panel mb-15">
    <h3><?= htmlspecialchars($p->getNombre(), ENT_QUOTES, 'UTF-8') ?></h3>
    <p><?= htmlspecialchars($p->getDescripcion(), ENT_QUOTES, 'UTF-8') ?></p>
    <p><strong>Precio base:</strong> <?= number_format((float)$p->getPrecio(), 2) ?> €</p>
    <p><strong>IVA:</strong> <?= (int)$p->getIVA() ?>%</p>
    <p><strong>Precio final:</strong> <?= number_format((float)$p->getPrecioFinal(), 2) ?> €</p>
    <p>
        <strong>Estado:</strong>
        <?php if ($p->isOfertado()): ?>
            <span class="texto-exito">Activo</span>
        <?php else: ?>
            <span class="texto-error">No ofertado</span>
        <?php endif; ?>
    </p>

    <div class="actions-inline mt-14">
        <a class="btn primary" href="editarProducto.php?id=<?= (int)$p->getId() ?>&categoria_id=<?= (int)$categoria_id ?>">Editar</a>

        <?php if ($p->isOfertado()): ?>
            <form method="post" action="borrarProducto.php" onsubmit="return confirm('¿Desactivar producto?');" class="d-inline">
                <input type="hidden" name="id" value="<?= (int)$p->getId() ?>">
                <input type="hidden" name="categoria_id" value="<?= (int)$categoria_id ?>">
                <button class="btn danger" type="submit">Desactivar</button>
            </form>
        <?php else: ?>
            <form method="post" action="activarProducto.php" class="d-inline">
                <input type="hidden" name="id" value="<?= (int)$p->getId() ?>">
                <input type="hidden" name="categoria_id" value="<?= (int)$categoria_id ?>">
                <button class="btn" type="submit">Activar</button>
            </form>
        <?php endif; ?>
    </div>
</article>