<?php
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';

$productos = ProductoDAO::getAllActivos();

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
    $productosSeleccionados = ProductoDAO::getProductosDeOferta($oferta->getId());
}
?>

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
    
    <!-- El descuento se calcula automáticamente por JS según el total -->
    <input type="hidden" id="descuentoCalculado" name="descuento"
           value="<?= htmlspecialchars($descuento ?: 5) ?>">

    <h3>Añadir productos dinámicamente</h3>

    <a class="btn-aceptar" id="aAddProduct" href="#">Añadir producto</a>

    <!--
        <template> — patrón del Ejemplo1V2 (formularioOfertasV2.js).
        JS clona este bloque cada vez que el usuario pulsa "Añadir producto".
        Las opciones se generan desde PHP con los productos reales de la BD.
    -->
    <template id="mySelectProductsTemplate">
        <div class="fila-producto-dinamica mt-6">
            <select name="mySelectProduct[]" class="form-control mb-1">
                <option value="" selected>Seleccione un producto</option>
                <?php foreach ($productos as $p): ?>
                <option value="<?= $p->getId() ?>"
                        data-precio="<?= $p->getPrecioFinal() ?>">
                    <?= htmlspecialchars($p->getNombre()) ?>
                    (<?= number_format($p->getPrecioFinal(), 2) ?> €)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </template>

    <div id="contenedorProductosDinamicos"></div>

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