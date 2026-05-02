<?php
$precio_total = 0;
$productos = ProductoDAO::getProductosDeOferta($oferta->getId());

$lista = array_map(function ($p) use (&$precio_total) {
    $precio = $p->getPrecioFinal();
    $cantidad = $p->cantidad;
    $precio_cant = $precio * $cantidad;

    $precio_total += $precio_cant;

    return escaparHtml($p->getNombre()) . " ($cantidad) " . round($precio_cant, 2) . '€';
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

$checked = in_array($oferta->getId(), $_SESSION['ofertas_seleccionadas']);
?>

<tr>
    <?php if ($modoSeleccion): ?>
        <td data-label="Seleccionar">
            <input type="radio"
                   name="oferta"
                   value="<?= (int)$oferta->getId() ?>"
                   <?= $checked ? 'checked' : '' ?>
                   <?= !$aplicable ? 'disabled' : '' ?>>
        </td>
    <?php endif; ?>

    <td data-label="Nombre">
        <a class="click"
           href="detalleOferta.php?id=<?= (int)$oferta->getId() ?>&return=<?= urlencode("../ofertas/ofertaCliente.php" . ($modoSeleccion ? "?modo=edicion" : "")) ?>">
            <?= escaparHtml($oferta->getNombre()) ?>
        </a>
    </td>

    <td data-label="Productos">
        <?= implode(', ', $lista) ?>
    </td>

    <td data-label="Descuento">
        <?= escaparHtml((string)$oferta->getDescuento()) ?>%
    </td>

    <?php if ($modoSeleccion): ?>
        <td data-label="Aplicable">
            <?= $aplicable
                ? '<span class="texto-ok">Sí</span>'
                : '<span class="texto-error">No</span>' ?>
        </td>
    <?php endif; ?>
</tr>
