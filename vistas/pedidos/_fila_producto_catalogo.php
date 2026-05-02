<tr>

<td data-label="Imagen">
<?php if ($p->getImagen()): ?>

<img
src="<?= escaparHtml(RUTA_APP . '/' . $p->getImagen()) ?>"
class="img-thumbnail"
alt="<?= escaparHtml($p->getNombre()) ?>">

<?php endif; ?>

</td>

<td data-label="Nombre">

<a
href="<?= RUTA_APP ?>/vistas/productos/detalle_producto.php?id=<?= $p->getId() ?>"
class="link-destacado">

<?= escaparHtml($p->getNombre()) ?>

</a>

</td>

<td data-label="Descripción">
    <?= escaparHtml($p->getDescripcion()) ?>
</td>

<td data-label="Precio" class="col-precio">
<?= $p->getPrecioFinal() ?> €
</td>

<td class="col-boton" data-label="Acción">
    <?= $formHtmls[$p->getId()] ?>
</td>

</tr>
