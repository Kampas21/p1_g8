<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();

// 🔒 Solo gerente
if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

// 📌 Obtener categoría
$categoria_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die('Categoría inválida');
}

$categoria = CategoriaService::getById($categoria_id);

if (!$categoria) {
    die('Categoría no encontrada');
}

// 📦 Obtener productos
$productos = ProductoService::getAllByCategoria($categoria_id);

// 🎨 Vista
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
<<<<<<< HEAD

<div class="productos-container">

<?php foreach ($productos as $p): ?>

    <div class="producto-card">

        <h3><?= htmlspecialchars($p->getNombre()) ?></h3>

        <p><?= htmlspecialchars($p->getDescripcion()) ?></p>

        <p><strong>Precio base:</strong> <?= number_format($p->getPrecio(), 2) ?> €</p>

        <p><strong>IVA:</strong> <?= $p->getIVA() ?>%</p>

        <p><strong>Precio final:</strong> <?= number_format($p->getPrecioFinal(), 2) ?> €</p>

        <p>
            <strong>Estado:</strong>
            <?php if ($p->isOfertado()): ?>
                <span style="color:green;">Activo</span>
            <?php else: ?>
                <span style="color:gray;">Inactivo</span>
=======
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
>>>>>>> 02a7ae2da73ba46f74f12d6b3ce5d1d78125ca97
            <?php endif; ?>
        </p>

<<<<<<< HEAD
        <div class="acciones">

            <a href="editarProducto.php?id=<?= $p->getId() ?>&categoria_id=<?= $categoria_id ?>">
                Editar
            </a>

            <?php if ($p->isOfertado()): ?>
                
                <form method="POST" action="../../scripts/productos/desactivarProducto.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $p->getId() ?>">
                    <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
                    <button type="submit" onclick="return confirm('¿Desactivar producto?')">
                        Desactivar
                    </button>
                </form>

            <?php else: ?>

                <form method="POST" action="../../scripts/productos/activarProducto.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $p->getId() ?>">
                    <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
                    <button type="submit">
                        Activar
                    </button>
                </form>

=======
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
>>>>>>> 02a7ae2da73ba46f74f12d6b3ce5d1d78125ca97
            <?php endif; ?>

        </div>

    </div>

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