<tr>

<td><?= (int)$cat->getId() ?></td>

<td><?= e($cat->getNombre()) ?></td>

<td><?= e($cat->getDescripcion()) ?></td>

<td>
<img
src="<?= RUTA_APP ?>/img/categorias/<?= e($cat->getImagen()) ?>"
width="70"
style="
border-radius:8px;
object-fit:cover;
">
</td>

<td>
<?php if ($cat->isActiva()): ?>
    <span class="texto-ok">Activa</span>
<?php else: ?>
    <span class="texto-error">Inactiva</span>
<?php endif; ?>
</td>

<td>
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