<?php
// Este script sirve de apoyo a la vista para quitar toda la complejidad del bucle y los botones.
// Recibe un array $productos y la variable $categoria_id de la vista que lo invoca.

if (empty($productos)): ?>
    <p>No hay productos en esta categoría.</p>
<?php else: ?>
    <div class="productos-container">
        <?php foreach ($productos as $p): ?>
            <?php require __DIR__ . '/../../vistas/productos/_tarjeta_producto.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>