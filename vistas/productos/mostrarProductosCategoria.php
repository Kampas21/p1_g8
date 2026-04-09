<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
    ?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>No tienes permisos para acceder a productos.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

$categoria_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die('Categoría inválida');
}

$productos = ProductoService::getAllByCategoria($categoria_id);

$tituloPagina = 'Productos';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Productos</h1>

<p>
    <a class="btn-nuevo" href="producto_form.php?modo=crear&categoria_id=<?= $categoria_id ?>">
        + Crear producto
    </a>
</p>

<?php if (empty($productos)): ?>
    <p>No hay productos en esta categoría.</p>
<?php else: ?>
    <?php foreach ($productos as $p): ?>
        <div class="panel">
            <h3><?= htmlspecialchars($p->getNombre()) ?></h3>

            <p><?= htmlspecialchars($p->getDescripcion()) ?></p>

            <p><strong>Precio base:</strong> <?= number_format($p->getPrecio(), 2) ?> €</p>
            <p><strong>IVA:</strong> <?= number_format($p->getIVA(), 0) ?> %</p>
            <p><strong>Precio final:</strong> <?= number_format($p->getPrecioFinal(), 2) ?> €</p>
            
            <?php if ($p->isOfertado() == 1): ?>
                <p class="texto-exito">Ofertado</p>
            <?php else: ?>
                <p class="texto-error">No ofertado</p>
            <?php endif; ?>

            <a class="btn editar"
               href="producto_form.php?id=<?= $p->getId() ?>&categoria_id=<?= $categoria_id ?>">
                Editar
            </a>

            <?php if ((int)$p->isOfertado() === 1): ?>
                <a class="btn borrar"
                   href="borrarProducto.php?id=<?= $p->getId() ?>&categoria_id=<?= $categoria_id ?>"
                   onclick="return confirm('¿Seguro que quieres desactivar este producto?')">
                    Eliminar
                </a>
            <?php else: ?>
                <a class="btn activar"
                   href="activarProducto.php?id=<?= $p->getId() ?>&categoria_id=<?= $categoria_id ?>">
                    Activar
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<p>
    <a class="btn-volver" href="../categorias/categoriasList.php">← Volver a categorías</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';