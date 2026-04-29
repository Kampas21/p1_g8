<tr>

<td><?= (int)$oferta->getId() ?></td>

<td>
    <a class="click"
       href="detalleOferta.php?id=<?= (int)$oferta->getId() ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
        <?= e($oferta->getNombre()) ?>
    </a>
</td>

<td>
<?= e(date('d/m/Y H:i', strtotime($oferta->getFechaInicio()))) ?>
</td>

<td>
<?= e(date('d/m/Y H:i', strtotime($oferta->getFechaFin()))) ?>
</td>

<td>
<?php if (!$oferta->estaActiva()): ?>
    <span class="text-danger">Caducada</span>
<?php else: ?>
    <span class="text-success">Activa</span>
<?php endif; ?>
</td>

<td>
<div class="actions-inline">

<a href="editarOferta.php?id=<?= (int)$oferta->getId() ?>"
class="btn small primary">
Editar
</a>

<a href="borrarOferta.php?id=<?= (int)$oferta->getId() ?>"
class="btn small danger"
onclick="return confirm('¿Borrar oferta?');">
Borrar
</a>

</div>
</td>

</tr>