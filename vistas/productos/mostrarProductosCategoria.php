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
    <a class="btn-nuevo" href="crearProducto.php?categoria_id=<?= $categoria_id ?>">
        + Crear producto
    </a>
</p>

<?php if (empty($productos)): ?>
    <p>No hay productos en esta categoría.</p>
<?php else: ?>
    <?php foreach ($productos as $p): ?>
        <div class="panel" style="margin-bottom:15px;">
            <h3><?= htmlspecialchars($p->getNombre()) ?></h3>

            <p><?= htmlspecialchars($p['descripcion']) ?></p>

<?php
$precioBase = $p->getPrecioBase();
$iva = $p->getIva();
$precioFinal = $p->getPrecioFinal();
?>

<p><strong>Precio base:</strong> <?= number_format($precioBase, 2) ?> €</p>
<p><strong>IVA:</strong> <?= number_format($iva, 0) ?> %</p>
<p><strong>Precio final:</strong> <?= number_format($precioFinal, 2) ?> €</p>
            <?php if ($p->getOfertado() == 1): ?>
                <p style="color:green;">Ofertado</p>
            <?php else: ?>
                <p style="color:red;">No ofertado</p>
            <?php endif; ?>

            <a class="btn editar"
               href="editarProducto.php?id=<?= $p['id'] ?>&categoria_id=<?= $categoria_id ?>">
                Editar
            </a>

            <?php if ((int)$p['ofertado'] === 1): ?>
                <a class="btn borrar"
                   href="borrarProducto.php?id=<?= $p['id'] ?>&categoria_id=<?= $categoria_id ?>"
                   onclick="return confirm('¿Seguro que quieres desactivar este producto?')">
                    Eliminar
                </a>
            <?php else: ?>
                <a class="btn activar"
                   href="activarProducto.php?id=<?= $p['id'] ?>&categoria_id=<?= $categoria_id ?>">
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