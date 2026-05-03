<div class="categoria-card">

<a href="catalogo.php?categoria=<?= (int)$cat->getId() ?>">

<img
src="<?= RUTA_APP ?>/img/categorias/<?= escaparHtml($cat->getImagen()) ?>"
alt="<?= escaparHtml($cat->getNombre()) ?>">

</a>

<h4>
<?= escaparHtml($cat->getNombre()) ?>
</h4>

<a
href="catalogo.php?categoria=<?= (int)$cat->getId() ?>"
class="btn primary">

Ver productos

</a>

</div>
