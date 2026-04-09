<?php declare(strict_types=1); ?>
<article class="panel mb-15">
    <h3><?= htmlspecialchars($p->getNombre()) ?></h3>
    <p><?= htmlspecialchars($p->getDescripcion()) ?></p>
    <p><strong>Precio base:</strong> <?= number_format($p->getPrecio(), 2) ?> €</p>
    <p><strong>IVA:</strong> <?= number_format((float)$p->getIVA(), 0) ?> %</p>
    <p><strong>Precio final:</strong> <?= number_format($p->getPrecioFinal(), 2) ?> €</p>
    
    <?= $p->isOfertado() ? '<p class="texto-exito">Ofertado</p>' : '<p class="texto-error">No ofertado</p>' ?>

    <div class="actions-inline mt-14">
        <a class="btn primary" href="producto_form.php?id=<?= $p->getId() ?>&categoria_id=<?= $categoria_id ?>">Editar</a>

        <?php if ((int)$p->isOfertado() === 1): ?>
            
            <form method="post" action="borrarProducto.php" onsubmit="return confirm('¿Seguro que quieres borrarlo?');" class="d-inline">
                <input type="hidden" name="id" value="<?= $p->getId() ?>">
                <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
                <button class="btn danger" type="submit">Eliminar</button>
            </form>
        <?php else: ?>
            <form method="post" action="activarProducto.php" class="d-inline">
                <input type="hidden" name="id" value="<?= $p->getId() ?>">
                <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
                <button class="btn" type="submit">Activar</button>
            </form>
        <?php endif; ?>
    </div>
</article>