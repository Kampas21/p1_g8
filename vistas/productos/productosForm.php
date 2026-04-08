<?php
$modo = $modo ?? 'crear';
?>

<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre"
        value="<?= $producto->getNombre() ?? '' ?>" required>

    <textarea name="descripcion"><?= $producto->getDescripcion() ?? '' ?></textarea>

    <input type="number" step="0.01" name="precio"
        value="<?= $producto->getPrecio() ?? '' ?>" required>

    <select name="iva">
        <option value="4">4%</option>
        <option value="10">10%</option>
        <option value="21">21%</option>
    </select>

    <input type="number" name="categoria_id"
        value="<?= $producto->getCategoriaId() ?? '' ?>" required>

    <button type="submit">
        <?= $modo === 'editar' ? 'Actualizar' : 'Crear' ?>
    </button>
</form>