<?php
require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../entities/categoria.php';
require_once __DIR__ . '/../../entities/pedido.php';


// Verificar que hay un pedido activo, si no redirigir a elegir tipo
$pedido = Pedido::getPedidoNuevo($usuario_id);
if (!$pedido) {
    header('Location: elegirTipo.php');
    exit();
}
$pedido_id = $pedido['id'];

// Procesar "Añadir al carrito"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = (int)($_POST['producto_id'] ?? 0);
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);

    if ($producto_id > 0) {
        $producto = Producto::getProductoById($producto_id);
        if ($producto) {
            $precio = Producto::getPrecioFinal($producto['precio_base'], $producto['iva']);
            Pedido::addProducto($pedido_id, $producto_id, $precio);
        }
    }

    // Redirigir de vuelta al catálogo de la misma categoría
    header('Location: catalogo.php' . ($categoria_id ? "?categoria=$categoria_id" : ''));
    exit();
}

// Mostrar categorías o productos según parámetro GET
$categoria_id = isset($_GET['categoria']) && is_numeric($_GET['categoria'])
    ? (int)$_GET['categoria']
    : null;

if ($categoria_id) {
    $productos = Producto::getProductosByCategoria($categoria_id);
    $categoria = Categoria::getCategoriaById($categoria_id);
} else {
    $categorias = Categoria::getCategorias();
}
?>

<h1>Catálogo</h1>

<?php if ($categoria_id): ?>

    <h2><?= htmlspecialchars($categoria['nombre']) ?></h2>
    <p><a href="catalogo.php">← Volver a categorías</a></p>

    <?php if (empty($productos)): ?>
        <p>No hay productos disponibles en esta categoría.</p>
    <?php else: ?>
        <table border="1">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th></th>
            </tr>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['descripcion']) ?></td>
                    <td><?= Producto::getPrecioFinal($p['precio_base'], $p['iva']) ?> €</td>
                    <td>
                        <form method="POST" action="catalogo.php">
                            <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
                            <button type="submit">+ Añadir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

<?php else: ?>

    <h2>Categorías</h2>
    <ul>
        <?php foreach ($categorias as $cat): ?>
            <li>
                <a href="catalogo.php?categoria=<?= $cat['id'] ?>">
                    <?= htmlspecialchars($cat['nombre']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>

<p><a href="carrito.php">🛒 Ver carrito</a></p>