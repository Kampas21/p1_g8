<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();
$modoSeleccion = PedidoService::carritoTieneProductos();

$pedido_productos = [];
if ($modoSeleccion) {
    foreach (PedidoService::getCarritoItems() as $producto_id => $item) {
        $pedido_productos[(int)$producto_id] = (int)($item['cantidad'] ?? 0);
    }
}

// 🔹 Ofertas activas
$ofertas = OfertaDAO::getAllActivas();

$tituloPagina = 'Ofertas disponibles';
ob_start();
?>

<h1>Ofertas disponibles</h1>

<?php if ($modoSeleccion): ?>
    <div class="info-ofertas">
        <strong class="info-ofertas-titulo">ℹ️ Información importante sobre las ofertas:</strong>

        <ul class="info-ofertas-lista">
            <li>Puedes seleccionar una o varias ofertas y aplicarlas al pedido actual.</li>
            <li><strong>Debes seleccionar todas las ofertas que quieras usar antes de pulsar "Aplicar ofertas", sino solo se seleccionaran de solo una oferta.</strong></li>
            <li>Cada oferta indica los productos y cantidades necesarias para poder aplicarse.</li>
            <li>Una oferta solo se aplicará si tu pedido contiene todos los productos requeridos en la cantidad suficiente.</li>
            <li>Las ofertas pueden aplicarse varias veces si se cumplen las condiciones (por ejemplo, varios packs).</li>
            <li>Un mismo producto no puede utilizarse en más de una oferta al mismo tiempo.</li>
            <li>Podrás ver el precio original del pedido y el ahorro total aplicado gracias a las ofertas.</li>
        </ul>

    </div>
<?php endif; ?>

<?php if ($modoSeleccion): ?>
    <form method="POST" action="../../scripts/ofertas/aplicarOfertas.php">
    <?php endif; ?>

    <div class="panel table-wrap">
        <table>
            <tr>
                <?php if ($modoSeleccion): ?>
                    <th>Seleccionar</th>
                <?php endif; ?>
                <th>Nombre</th>
                <!-- <th>Descripción</th> -->
                <th>Productos</th>
                <!-- <th>Precio pack</th> -->
                <th>Descuento</th>
                <!-- <th>Precio final</th> -->
                <?php if ($modoSeleccion): ?>
                    <th>Aplicable</th>
                <?php endif; ?>
            </tr>

            <?php foreach ($ofertas as $oferta): ?>
                <?php
                // $precio_total = 0;
                $productos = ProductoDAO::getProductosDeOferta($oferta->getId());

                $lista = array_map(function ($p) use (&$precio_total) {
                    $precio = $p->getPrecioFinal();
                    $cantidad = $p->cantidad;
                    $precio_cant = $precio * $cantidad;

                    $precio_total += $precio_cant;

                    return $p->getNombre() . " ($cantidad) " . round($precio_cant, 2) . '€';
                }, $productos);

                // $precio_final = $oferta->aplicarDescuento($precio_total);

                $aplicable = true;

                if ($modoSeleccion) {
                    foreach ($productos as $p) {
                        $id = $p->getId();
                        if (!isset($pedido_productos[$id]) || $pedido_productos[$id] < $p->cantidad) {
                            $aplicable = false;
                            break;
                        }
                    }
                }
                ?>

                <tr>
                    <?php if ($modoSeleccion): ?>
                        <td>
                            <input type="checkbox"
                                name="ofertas[]"
                                value="<?= $oferta->getId() ?>"
                                <?= !$aplicable ? 'disabled' : '' ?>>
                        </td>
                    <?php endif; ?>

                    <td>
                        <a class="click"
                            href="detalleOferta.php?id=<?= $oferta->getId() ?>&return=<?= "../pedidos/carrito.php" ?>">
                            <?= htmlspecialchars($oferta->getNombre()) ?>
                        </a>
                    </td>
                    <!-- <td><?= htmlspecialchars($oferta->getDescripcion()) ?></td> -->

                    <td><?= implode(', ', $lista) ?></td>

                    <!-- <td>?= round($precio_total, 2) ?>€</td> -->
                    <td><?= $oferta->getDescuento() ?>%</td>
                    <!-- <td>?= round($precio_final, 2) ?>€</td> -->

                    <?php if ($modoSeleccion): ?>
                        <td>
                            <?= $aplicable
                                ? '<span style="color:green">Sí</span>'
                                : '<span style="color:red">No</span>' ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <br>

    <?php if ($modoSeleccion): ?>
        <button class="btn-aceptar" type="submit">Aplicar ofertas</button>
    </form>
<?php endif; ?>

<p>
    <?php if ($modoSeleccion): ?>
        <a class="btn-volver" href="../pedidos/carrito.php">Volver</a>
    <?php else: ?>
        <a class="btn-volver" href="../../index.php">Volver</a>
    <?php endif; ?>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>