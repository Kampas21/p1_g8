<tr>

<td data-label="ID"><?= (int)$cat->getId() ?></td>

<td data-label="Nombre"><?= escaparHtml($cat->getNombre()) ?></td>

<td data-label="Descripción"><?= escaparHtml($cat->getDescripcion()) ?></td>

<td data-label="Imagen">
<img
src="<?= RUTA_APP ?>/img/categorias/<?= escaparHtml($cat->getImagen()) ?>"
width="70"
class="img-rounded">
</td>

<td data-label="Estado">
<?php if ($cat->isActiva()): ?>
    <span class="texto-ok">Activa</span>
<?php else: ?>
    <span class="texto-error">Inactiva</span>
<?php endif; ?>
</td>

<td data-label="Acciones">
<div class="actions-inline">

<a href="editarCategoria.php?id=<?= (int)$cat->getId() ?>"
class="btn small primary">
Editar
</a>

<a href="../productos/mostrarProductosCategoria.php?id=<?= (int)$cat->getId() ?>"
class="btn small prod">
Productos
</a>

<?php if ($cat->isActiva()): ?>

<form method="post"
action="../../scripts/categorias/borrarCategoria.php"
onsubmit="return confirm('¿Desactivar categoría?');"
class="d-inline">

<input type="hidden"
name="id"
value="<?= (int)$cat->getId() ?>">

<button class="btn small danger" type="submit">
Desactivar
</button>

</form>

<?php else: ?>

<form method="post"
action="../../scripts/categorias/activarCategoria.php"
class="d-inline">

<input type="hidden"
name="id"
value="<?= (int)$cat->getId() ?>">

<button class="btn small" type="submit">
Activar
</button>

</form>

<?php endif; ?>

</div>
</td>

</tr>