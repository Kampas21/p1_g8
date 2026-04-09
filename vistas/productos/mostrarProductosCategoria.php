<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();

//Solo gerente
if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

//Obtener categoría
$categoria_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die('Categoría inválida');
}

$categoria = CategoriaService::getById($categoria_id);

if (!$categoria) {
    die('Categoría no encontrada');
}

//Obtener productos
$productos = ProductoService::getAllByCategoria($categoria_id);

//Vista
$tituloPagina = 'Productos';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Productos de la categoría: <?= htmlspecialchars($categoria->getNombre()) ?></h1>

<p>
    <a class="btn-nuevo" href="producto_form.php?modo=crear&categoria_id=<?= $categoria_id ?>">
        + Crear producto
    </a>
</p>

<?php if (empty($productos)): ?>
    <p>No hay productos en esta categoría.</p>
<?php else: ?>

<div class="productos-container">

<?php foreach ($productos as $p): ?>
    <?php include __DIR__ . '/_tarjeta_producto.php'; ?>
<?php endforeach; ?>

</div>

<?php endif; ?>

<p style="margin-top:20px;">
    <a class="btn-volver" href="../categorias/categoriasList.php">
        ← Volver a categorías
    </a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';