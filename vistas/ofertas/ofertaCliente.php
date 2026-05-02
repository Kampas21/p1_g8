<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';
require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/util.php';

$user = require_login();

$modoSeleccion = ($_GET['modo'] ?? '') === 'edicion';

$pedido_productos = [];

if ($modoSeleccion) {
    foreach (PedidoService::getCarritoItems() as $item) {
        if (!empty($item['es_recompensa'])) continue;
        $pid = (int)$item['producto_id'];
        $pedido_productos[$pid] = ($pedido_productos[$pid] ?? 0) + (int)($item['cantidad'] ?? 0);
    }
}

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
    <table class="tabla-movil">
        <thead>
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
        </thead>

        <tbody>
        <?php foreach ($ofertas as $oferta): ?>
            <?php require __DIR__ . '/_fila_oferta_cliente.php'; ?>
        <?php endforeach; ?>
        </tbody>
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