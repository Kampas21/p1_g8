<?php
require_once '../../includes/productoService.php';

$categoria_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$productos = ProductoService::getAllByCategoria($categoria_id);
?>

<h2>Productos</h2>

<?php foreach ($productos as $p): ?>
    <div style="border:1px solid #ccc; margin:10px; padding:10px;">

        <h3><?= $p->getNombre() ?></h3>
        <p><?= $p->getDescripcion() ?></p>
        <p><?= $p->getPrecio() ?> €</p>

        <?php if ($p->isOfertado()): ?>
            <span style="color:green;">Disponible</span>
        <?php else: ?>
            <span style="color:red;">No ofertado</span>
        <?php endif; ?>

        <br><br>

        <!-- EDITAR -->
        <a href="editarProducto.php?id=<?= $p->getId() ?>">Editar</a>

        <!-- BORRAR (DESACTIVAR) -->
        <?php if ($p->isOfertado()): ?>
            <form method="POST" action="borrarProducto.php">
                <input type="hidden" name="id" value="<?= $p->getId() ?>">
                <button>Eliminar</button>
            </form>
        <?php endif; ?>

        <!-- ACTIVAR -->
        <?php if (!$p->isOfertado()): ?>
            <form method="POST" action="activarProducto.php">
                <input type="hidden" name="id" value="<?= $p->getId() ?>">
                <button>Activar</button>
            </form>
        <?php endif; ?>

    </div>
<?php endforeach; ?>

<br>
<a href="crearProducto.php"> Crear producto</a>