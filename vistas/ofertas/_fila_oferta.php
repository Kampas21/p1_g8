<tr>

<td data-label="ID"><?= (int)$oferta->getId() ?></td>

<td data-label="Nombre">
    <a class="click"
       href="detalleOferta.php?id=<?= (int)$oferta->getId() ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
        <?= escaparHtml($oferta->getNombre()) ?>
    </a>
</td>

<td data-label="Fecha inicio">
<?= escaparHtml(date('d/m/Y H:i', strtotime($oferta->getFechaInicio()))) ?>
</td>

<td data-label="Fecha fin">
<?= escaparHtml(date('d/m/Y H:i', strtotime($oferta->getFechaFin()))) ?>
</td>

<td data-label="Estado">
<?php if (!$oferta->estaActiva()): ?>
    <span class="text-danger">Caducada</span>
<?php else: ?>
    <span class="text-success">Activa</span>
<?php endif; ?>
</td>

<td data-label="Acciones">
<div class="actions-inline">

<a href="editarOferta.php?id=<?= (int)$oferta->getId() ?>"
class="btn small primary">
Editar
</a>

<form method="post"
action="../../scripts/ofertas/borrarOferta.php"
onsubmit="return confirm('¿Borrar oferta?');"
class="d-inline">

<input type="hidden"
name="id"
value="<?= (int)$oferta->getId() ?>">

<button class="btn small danger" type="submit">
Borrar
</button>

</form>


</div>
</td>

</tr>