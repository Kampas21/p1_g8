<?php
require_once __DIR__ . '/../../entities/categoria.php';

$categoria_id = $_GET['id'] ?? null;

$modoEdicion = isset($producto);

$action = $modoEdicion
    ? "editarProducto.php?id=" . $producto['id']
    : "crearProducto.php?id=$categoria_id";

$nombre = $modoEdicion ? $producto['nombre'] : '';
$descripcion = $modoEdicion ? $producto['descripcion'] : '';
$categoria = $modoEdicion ? $producto['categoria_id'] : $categoria_id;
$precio_base = $modoEdicion ? $producto['precio_base'] : '';
$iva = $modoEdicion ? $producto['iva'] : '';
$disponible = $modoEdicion ? $producto['disponible'] : '';
$ofertado = $modoEdicion ? $producto['ofertado'] : '';

$categorias = Categoria::getCategorias();
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1><?= $modoEdicion ? 'Editar Producto' : 'Nuevo Producto' ?></h1>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data">

    <p>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
    </p>

    <p>
        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="5" cols="40" required><?= htmlspecialchars($descripcion) ?></textarea>
    </p>

    <p>
        <label>Categoría:</label><br>

        <select name="categoria_id" required <?= !$modoEdicion ? 'disabled' : '' ?>>

            <?php foreach ($categorias as $cat): ?>

                <option value="<?= $cat['id'] ?>" <?= $categoria == $cat['id'] ? 'selected' : '' ?>>

                    <?= htmlspecialchars($cat['nombre']) ?>

                </option>

            <?php endforeach; ?>

        </select>

        <?php if (!$modoEdicion): ?>
            <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">
        <?php endif; ?>

    </p>

    <p>
        <label>Precio base:</label><br>
        <input type="number" name="precio_base" value="<?= htmlspecialchars($precio_base) ?>" step="0.01" min="0" required>
    </p>

    <p>
        <label>IVA:</label><br>
        <select name="iva" required>
            <option value="4" <?= $iva == 4 ? 'selected' : '' ?>>4%</option>
            <option value="10" <?= $iva == 10 ? 'selected' : '' ?>>10%</option>
            <option value="21" <?= $iva == 21 ? 'selected' : '' ?>>21%</option>
        </select>
    </p>

    <p>
        <label>Disponible:</label><br>
        <input type="number" name="disponible" value="<?= htmlspecialchars($disponible) ?>" min="0" required>
    </p>

    <p>
        <label>Ofertado:</label><br>

        <?php if ($modoEdicion): ?>
            <?= $ofertado ? 'Sí' : 'No' ?>
            <input type="hidden" name="ofertado" value="<?= $ofertado ?>">
            
        <?php else: ?>

            <select name="ofertado" required>
                <option value="1" <?= $ofertado == 1 ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $ofertado == 0 ? 'selected' : '' ?>>No</option>
            </select>
        <?php endif; ?>

    </p>

    <p>
        <label>Imagen:</label><br>
        <input type="file" name="imagen" accept="image/*">
    </p>

    <p><button type="submit" class="btn-aceptar">Guardar</button></p>

</form>