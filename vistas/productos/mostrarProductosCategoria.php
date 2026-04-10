<?php


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    http_response_code(403);
    die('Acceso denegado');
}

$categoria_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$categoria_id) {
    http_response_code(400);
    die('Categoría inválida');
}

$categoria = CategoriaService::getById($categoria_id);
if (!$categoria) {
    http_response_code(404);
    die('Categoría no encontrada');
}

require_once __DIR__ . '/../../scripts/productos/cargarProductosCategoria.php';

$tituloPagina = 'Productos';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Productos de la categoría: <?= htmlspecialchars($categoria->getNombre(), ENT_QUOTES, 'UTF-8') ?></h1>

<p>
    <a class="btn-nuevo" href="crearProducto.php?categoria_id=<?= (int)$categoria_id ?>">+ Nuevo producto</a>
</p>

<?php if (empty($productos)): ?>
    <p>No hay productos en esta categoría.</p>
<?php else: ?>
    <div class="productos-container">
        <?php foreach ($productos as $p): ?>
            <?php require __DIR__ . '/_tarjeta_producto.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p class="mt-20">
    <a class="btn-volver" href="../categorias/categoriasList.php">← Volver a categorías</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';