<?php
require_once __DIR__ . '/../scripts/productos/cargarCatalogo.php';

$tituloPagina = 'Catálogo';
$rutaCSS = '../CSS/estilo.css';

ob_start();
?>

<h1>Catálogo de productos</h1>

<?php foreach ($catalogo as $bloque): ?>
    
    <h2><?= htmlspecialchars($bloque['categoria']->getNombre()) ?></h2>

    <?php foreach ($bloque['productos'] as $p): ?>
        
        <div class="producto-card">
            <h3><?= htmlspecialchars($p->getNombre()) ?></h3>
            <p><?= htmlspecialchars($p->getDescripcion()) ?></p>
            <p><?= number_format($p->getPrecio(), 2) ?>€</p>

            <form method="POST" action="../scripts/pedidos/addCarrito.php">
                <input type="hidden" name="producto_id" value="<?= $p->getId() ?>">
                <input type="number" name="cantidad" value="1" min="1">
                <button type="submit">Añadir</button>
            </form>
        </div>

    <?php endforeach; ?>

<?php endforeach; ?>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../includes/plantilla.php';
?>