<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();

$modoSeleccion = ($_GET['modo'] ?? '') === 'edicion';


$pedido_productos = [];

if ($modoSeleccion) {
    foreach (PedidoService::getCarritoItems() as $producto_id => $item) {
        $pedido_productos[(int)$producto_id] = (int)($item['cantidad'] ?? 0);
    }
}

/**
 * 🔥 estado de ofertas seleccionadas (persistente)
 */
if (!isset($_SESSION['ofertas_seleccionadas'])) {
    $_SESSION['ofertas_seleccionadas'] = [];
}

$ofertas = OfertaDAO::getAllActivas();

$tituloPagina = 'Ofertas disponibles';
ob_start();
?>

<h1>Ofertas disponibles</h1>

<?php if ($modoSeleccion): ?>
    <div class="info-ofertas">
        <strong>ℹ️ Información importante sobre las ofertas:</strong>

        <ul>
            <li>Solo puedes seleccionar una oferta por envío.</li>
            <li>Las ofertas se van acumulando si las añades una a una.</li>
            <li><strong>Si seleccionas una ya marcada, se desmarca.</strong></li>
            <li>Una oferta se puede multiplicar si tienes los productos necesarios de manera automática.</li>
            <li>No se pueden usar productos en más de una oferta a la vez.</li>
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
            <th>Productos</th>
            <th>Descuento</th>
            <?php if ($modoSeleccion): ?>
                <th>Aplicable</th>
            <?php endif; ?>
        </tr>

        <?php foreach ($ofertas as $oferta): ?>

            <?php
            $precio_total = 0;
            $productos = ProductoDAO::getProductosDeOferta($oferta->getId());

            $lista = array_map(function ($p) use (&$precio_total) {
                $precio = $p->getPrecioFinal();
                $cantidad = $p->cantidad;
                $precio_cant = $precio * $cantidad;

                $precio_total += $precio_cant;

                return $p->getNombre() . " ($cantidad) " . round($precio_cant, 2) . '€';
            }, $productos);

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

            /**
             * 🔥 comprobar si está seleccionada (estado sesión)
             */
            $checked = in_array($oferta->getId(), $_SESSION['ofertas_seleccionadas']);
            ?>

            <tr>
                <?php if ($modoSeleccion): ?>
                    <td>
                        <input type="radio"
                            name="oferta"
                            value="<?= $oferta->getId() ?>"
                            <?= $checked ? 'checked' : '' ?>
                            <?= !$aplicable ? 'disabled' : '' ?>>
                    </td>
                <?php endif; ?>

                <td>
                    <a class="click"
                            href="detalleOferta.php?id=<?= $oferta->getId() ?>&return=<?= urlencode("../ofertas/ofertaCliente.php" . ($modoSeleccion ? "?modo=edicion" : "")) ?>">
                            <?= htmlspecialchars($oferta->getNombre()) ?>
                    </a>
                </td>

                <td><?= implode(', ', $lista) ?></td>

                <td><?= $oferta->getDescuento() ?>%</td>

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
    <button class="btn-aceptar" type="submit">
        Añadir / Quitar oferta
    </button>
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