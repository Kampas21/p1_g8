<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/ofertaService.php';
require_once __DIR__ . '/../../entities/pedido.php';

$user = current_user();

if (!$user) {
    die("Debes iniciar sesión");
}

// 🔹 Detectar si viene de pedido
$pedido_id = $_GET['pedido_id'] ?? null;
$modoSeleccion = $pedido_id !== null;

// 🔹 Obtener productos del pedido
$pedido_productos = [];

if ($modoSeleccion) {
    $productos = PedidoService::getProductosPedido($pedido_id);

    foreach ($productos as $p) {
        $pedido_productos[$p->getProductoId()] = $p->getCantidad();
    }
}

// 🔹 Ofertas activas
$ofertas = OfertaService::getAllActivas();

$tituloPagina = 'Ofertas disponibles';
ob_start();
?>

<h1>Ofertas disponibles</h1>

<?php if ($modoSeleccion): ?>
<form method="POST" action="aplicarOfertas.php">
    <input type="hidden" name="pedido_id" value="<?= $pedido_id ?>">
<?php endif; ?>

<table border="1">
    <tr>
        <?php if ($modoSeleccion): ?>
            <th>Seleccionar</th>
        <?php endif; ?>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Productos</th>
        <th>Precio pack</th>
        <th>Descuento</th>
        <th>Precio final</th>
        <?php if ($modoSeleccion): ?>
            <th>Aplicable</th>
        <?php endif; ?>
    </tr>

<?php foreach ($ofertas as $oferta): ?>

    <?php
    $precio_total = 0;
    $productos = ProductoService::getProductosDeOferta($oferta->getId());

    $lista = array_map(function ($p) use (&$precio_total) {
        $precio = $p->getPrecioFinal();
        $cantidad = $p->cantidad;
        $precio_cant = $precio * $cantidad;

        $precio_total += $precio_cant;

        return $p->getNombre() . " ($cantidad) " . round($precio_cant, 2) . '€';
    }, $productos);

    $precio_final = $oferta->aplicarDescuento($precio_total);

    $aplicable = true;

    if ($modoSeleccion) {
        foreach ($productos as $p) {
            $id = $p->getId();
            if (!isset($pedido_productos[$id]) || 
                $pedido_productos[$id] < $p->cantidad) {
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

        <td><?= htmlspecialchars($oferta->getNombre()) ?></td>
        <td><?= htmlspecialchars($oferta->getDescripcion()) ?></td>

        <td><?= implode(', ', $lista) ?></td>

        <td><?= round($precio_total, 2) ?>€</td>
        <td><?= $oferta->getDescuento() ?>%</td>
        <td><?= round($precio_final, 2) ?>€</td>

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

<br>

<?php if ($modoSeleccion): ?>
    <button type="submit">Aplicar ofertas</button>
</form>
<?php endif; ?>

<p>
    <a class="btn-volver" href="../../index.php">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';