<tr>
    <td>
        <img src="<?= escaparHtml(RUTA_APP . '/' . $producto->getImagen()) ?>" class="img-thumbnail" alt="<?= escaparHtml($producto->getNombre()) ?>">
    </td>

    <td>
        <a href="<?= RUTA_APP ?>/vistas/productos/detalle_producto.php?id=<?= $producto->getId() ?>" class="click">
            <?= escaparHtml($producto->getNombre()) ?>
        </a>
        <?php if ($esRecompensa): ?>
            <br><small><strong>(Recompensa)</strong></small>
        <?php endif; ?>
    </td>

    <td class="col-precio">
        <?php if ($esRecompensa): ?>
            <?= escaparHtml((string)$bistrocoinsUnitarios) ?> BC
        <?php else: ?>
            <?= escaparHtml((string)$precio) ?> €
        <?php endif; ?>
    </td>

    <td>
        <?php if ($esRecompensa): ?>
            <?= escaparHtml((string)$cantidad) ?>
        <?php else: ?>
            <?= $formsActualizarHtml[$clave] ?? escaparHtml((string)$cantidad) ?>
        <?php endif; ?>
    </td>

    <td class="col-precio">
        <?php if ($esRecompensa): ?>
            <?= escaparHtml((string)($bistrocoinsUnitarios * $cantidad)) ?> BC
        <?php else: ?>
            <?= round($precio * $cantidad, 2) ?> €
        <?php endif; ?>
    </td>
    
    <td>
        <?= $formsEliminarHtml[$clave] ?? '' ?>
    </td>
</tr>
