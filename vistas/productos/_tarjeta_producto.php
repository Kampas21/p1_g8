<article class="producto-card panel mb-15">

<div style="display:flex; gap:20px; align-items:flex-start;">

<div style="flex-shrink:0;">
<img
src="../../<?= trim($p->getImagen()) ?>"
alt="<?= htmlspecialchars($p->getNombre(), ENT_QUOTES,'UTF-8') ?>"
width="140"
height="100"
style="
object-fit:cover;
border-radius:8px;
border:1px solid #ccc;
display:block;
">
</div>

<div style="flex:1;">

<h3><?= htmlspecialchars($p->getNombre()) ?></h3>

<p>
<?= htmlspecialchars($p->getDescripcion()) ?>
</p>

<p>
<strong>Precio base:</strong>
<?= number_format((float)$p->getPrecio(),2) ?> €
</p>

<p>
<strong>IVA:</strong>
<?= (int)$p->getIVA() ?>%
</p>

<p>
<strong>Precio final:</strong>
<?= number_format((float)$p->getPrecioFinal(),2) ?> €
</p>

<p>
<strong>Estado:</strong>
<?= $p->isOfertado() ? 'Activo' : 'No ofertado' ?>
</p>

<a
class="btn primary"
href="crearProducto.php?id=<?= (int)$p->getId() ?>&categoria_id=<?= (int)$categoria_id ?>">
Editar
</a>

</div>

</div>

</article>