<?php
require_once __DIR__ . '/../../entities/categoria.php';

$modoEdicion = isset($producto);
$action = $modoEdicion ? "editarProducto.php?id=" . $producto['id'] : "crearProducto.php";
$nombre = $modoEdicion ? $producto['nombre'] : '';
$descripcion = $modoEdicion ? $producto['descripcion'] : '';
$categoria = $modoEdicion ? $producto['categoria_id'] : '';
$precio_base = $modoEdicion ? $producto['precio_base'] : '';
$iva = $modoEdicion ? $producto['iva'] : '';
$disponible = $modoEdicion ? $producto['disponible'] : '';
$ofertado = $modoEdicion ? $producto['ofertado'] : '';

$categorias = Categoria::getCategorias();
?>

<h1><?= $modoEdicion ? 'Editar Producto' : 'Nuevo Producto' ?></h1>

<form method="POST" action="<?= $action ?>">
    <p>
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
    </p>

    <p>
        <label for="descripcion">Descripción:</label><br>
        <textarea name="descripcion" id="descripcion" rows="5" cols="40" required><?= htmlspecialchars($descripcion) ?></textarea>
    </p>
    <p>
        <label for="categoria_id">Categoria:</label><br>
        <select name="categoria_id" id="categoria_id" required>
            <option value="">-- Selecciona una categoría --</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoria == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label for="precio_base">Precio base:</label><br>
        <input type="number" name="precio_base" id="precio_base" value="<?= htmlspecialchars($precio_base) ?>" step="0.01" min="0" required>
    </p>
    <p>
        <label for="iva">IVA:</label><br>
        <input type="number" name="iva" id="iva" value="<?= htmlspecialchars($iva) ?>" step="0.01" min="0" required>
    </p>

    <p>
        <label for="disponible">Disponible:</label><br>
         <input type="number" name="disponible" id="disponible" value="<?= htmlspecialchars($disponible) ?>" step="1" min="0" required>
    </p>
    <p>
        <label for="ofertado">Ofertado:</label><br>
        <select name="ofertado" id="ofertado" required>
            <option value="1" <?= $ofertado == 1 ? 'selected' : '' ?>>Sí</option>
            <option value="0" <?= $ofertado == 0 ? 'selected' : '' ?>>No</option>
        </select>
    </p>


    <button type="submit">Guardar</button>
</form>

<p>
    <a href="productosList.php">Volver al listado</a>
</p>