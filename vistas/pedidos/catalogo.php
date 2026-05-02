<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/CategoriaDAO.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../entities/Producto.php';
require_once __DIR__ . '/../../entities/Categoria.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioAddCarrito.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

$user = require_login();
if (!PedidoService::carritoTieneTipo()) {
    redirect('elegirTipo.php');
}

$categoria_id = isset($_GET['categoria']) && is_numeric($_GET['categoria'])
    ? (int)$_GET['categoria']
    : null;

$formHtmls = [];

if ($categoria_id) {

    $productos = ProductoDAO::getAllActivosByCategoria($categoria_id);

    $categoria = CategoriaDAO::getById($categoria_id);

    if (!empty($productos)) {

        foreach ($productos as $p) {

            $prod_id = (int)$p->getId();

            $formAdd =
                new \es\ucm\fdi\aw\Formulario\FormularioAddCarrito(
                    $prod_id,
                    $categoria_id
                );

            $formHtmls[$prod_id] = $formAdd->gestiona();
        }
    }

} else {

    $categorias = CategoriaDAO::getAll();

}

$titulo = $categoria_id && isset($categoria)
    ? 'Catálogo — ' . escaparHtml($categoria->getNombre())
    : 'Catálogo';

$tituloPagina = $titulo . ' | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<main>

<?php foreach (flash_get_all() as $f): ?>
<div class="mensaje-<?= escaparHtml($f['type']) ?>">
<?= escaparHtml($f['message']) ?>
</div>
<?php endforeach; ?>


<div class="panel">

<?php if ($categoria_id): ?>

<div class="actions-inline mb-12">
<a href="catalogo.php" class="btn">
← Categorías
</a>

<a href="carrito.php" class="btn primary">
🛒 Ver carrito
</a>
</div>

<h2><?= escaparHtml($categoria->getNombre()) ?></h2>

<?php if (empty($productos)): ?>

<p>No hay productos disponibles en esta categoría.</p>

<?php else: ?>

<div class="table-wrap">

<table class="tabla-productos-movil">
<thead>
<tr>
<th>Imagen</th>
<th>Nombre</th>
<th>Descripción</th>
<th>Precio</th>
<th></th>
</tr>
</thead>

<tbody>

<?php foreach ($productos as $p): ?>

                <?php require __DIR__ . '/_fila_producto_catalogo.php'; ?>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>


<?php else: ?>

<div class="actions-inline mb-12">

<a href="carrito.php" class="btn primary">
🛒 Ver carrito
</a>

</div>


<h2>Elige una categoría</h2>


<div class="categoria-grid">

<?php foreach ($categorias as $cat): ?>

                <?php require __DIR__ . '/_tarjeta_categoria_catalogo.php'; ?>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>