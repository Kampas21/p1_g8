<?php
//require_once __DIR__ . '/../../entities/oferta.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';

$productos = ProductoService::getAllActivos();

$modoEdicion = isset($oferta);

$action = $modoEdicion
    ? "editarOferta.php?id=" . $oferta->getId()
    : "crearOferta.php";

$nombre = $modoEdicion ? $oferta->getNombre() : '';
$descripcion = $modoEdicion ? $oferta->getDescripcion() : '';
$descuento = $modoEdicion ? $oferta->getDescuento() : '';

$fecha_inicio = $modoEdicion && $oferta->getFechaInicio()
    ? date('Y-m-d\TH:i', strtotime($oferta->getFechaInicio()))
    : '';

$fecha_fin = $modoEdicion && $oferta->getFechaFin()
    ? date('Y-m-d\TH:i', strtotime($oferta->getFechaFin()))
    : '';



$productosSeleccionados = [];

if ($modoEdicion) {
    $productosSeleccionados = ProductoService::getProductosDeOferta($oferta->getId());
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

        <!-- <label>Precio con descuento aplicado, solo se pueden dos decimales:</label><br>
        <input type="number" name="precio_des" step="0.01" min="0"
            value="<?= htmlspecialchars($precio_des) ?>" required> -->
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
                    if ($ps->getId() == $p->getId()) {
                        $cantidad = $ps->cantidad;
                        break;
                    }
                }
            }

        ?>

            <tr>

                <?php if ($modoEdicion): ?>
                    <td>
                        <?= $cantidad > 0
                            ? '<span class="text-success">✔</span>'
                            : '<span class="text-gray">—</span>' ?>
                    </td>
                <?php endif; ?>


                <td><?= htmlspecialchars($p->getNombre()) ?></td>
                <td><?= number_format($p->getPrecioFinal(), 2) ?> €</td>

                <td>
                    <input type="number"
                        name="cantidades[<?= $p->getId() ?>]"
                        value="<?= $cantidad ?>"
                        min="0">
                </td>
            </tr>

        <?php endforeach; ?>
    </table>

    <h3>Resumen</h3>

    <p>Total: <span id="precioTotal">0</span> €</p>
    <p>Descuento: <span id="descuentoTxt">0</span> %</p>
    <p>Precio final: <span id="precioFinal">0</span> €</p>

    <p>
        <button type="submit" class="btn-aceptar">Guardar</button>
    </p>

</form>


<p>
    <a class="btn-volver" href="listarOfertas.php">
        Volver al listado
    </a>
</p>

<script src="../../JS/ofertas.js"></script>