<?php
require_once __DIR__ . '/../../entities/oferta.php';

require_once __DIR__ . '/../../entities/producto.php';

$productos = Producto::getProductos();

$modoEdicion = isset($oferta);

$action = $modoEdicion
    ? "editarOferta.php?id=" . $oferta['id']
    : "crearOferta.php";

$nombre = $modoEdicion ? $oferta['nombre'] : '';
$descripcion = $modoEdicion ? $oferta['descripcion'] : '';

$fecha_inicio = $modoEdicion && !empty($oferta['fecha_inicio'])
    ? date('Y-m-d\TH:i', strtotime($oferta['fecha_inicio']))
    : '';
$fecha_fin = $modoEdicion && !empty($oferta['fecha_fin'])
    ? date('Y-m-d\TH:i', strtotime($oferta['fecha_fin']))
    : '';

$descuento = $modoEdicion ? $oferta['descuento'] : '';

$productosSeleccionados = [];

if ($modoEdicion) {
    $productosSeleccionados = Oferta::getProductosDeOferta($oferta['id']);
}
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1><?= $modoEdicion ? 'Editar Oferta' : 'Nueva Oferta' ?></h1>

<form method="POST" action="<?= $action ?>">

    <p>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
    </p>

    <p>
        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="5" cols="40"><?= htmlspecialchars($descripcion) ?></textarea>
    </p>

    <p>
        <label>Fecha inicio:</label>
        <input type="datetime-local" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>" required>
    </p>

    <p>
        <label>Fecha fin:</label>
        <input type="datetime-local" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>" required>
    </p>

    <p>
        <label>Descuento (%), del 1 al 100:</label><br>
        <input type="number" name="descuento" step="0.01" min="0" max="100"
            value="<?= htmlspecialchars($descuento) ?>" required>
    </p>



    <h3>Productos de la oferta</h3>

    <table border="1">
        <tr>
            <?php
            if ($modoEdicion) {
                echo '<th>Seleccionados</th>';
            }
            ?>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
        </tr>

        <?php foreach ($productos as $p):
            $cantidad = 0;

            if ($modoEdicion) {
                foreach ($productosSeleccionados as $ps) {
                    if ($ps['producto_id'] == $p['id']) {
                        $cantidad = $ps['cantidad'];
                        break;
                    }
                }
            }

        ?>

            <tr>

                <?php if ($modoEdicion): ?>
                    <td>
                        <?= $cantidad > 0
                            ? '<span style="color:green">✔</span>'
                            : '<span style="color:gray">—</span>' ?>
                    </td>
                <?php endif; ?>


                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= number_format(Producto::getPrecioFinal($p['precio_base'], $p['iva']), 2) ?> €</td>

                <td>
                    <input type="number"
                        name="cantidades[<?= $p['id'] ?>]"
                        value="<?= $cantidad ?>"
                        min="0">
                </td>
            </tr>

        <?php endforeach; ?>
    </table>

    <p>
        <button type="submit" class="btn-aceptar">Guardar</button>
    </p>

</form>

<p>
    <a class="btn-volver" href="listarOfertas.php">
        Volver al listado
    </a>
</p>