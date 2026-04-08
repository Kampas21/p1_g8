<?php
require_once '../../includes/productoService.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$producto = ProductoService::getById($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = filter_input(INPUT_POST, 'nombre');
    $descripcion = filter_input(INPUT_POST, 'descripcion');
    $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);

    ProductoService::actualizar($id, $nombre, $descripcion, $categoria_id, $precio, $iva);

    header("Location: categoriasList.php");
    exit;
}
?>

<h2>Editar producto</h2>

<form method="POST">
    <input name="nombre" value="<?= $producto->getNombre() ?>"><br>
    <textarea name="descripcion"><?= $producto->getDescripcion() ?></textarea><br>
    <input name="categoria_id" value="<?= $producto->getCategoriaId() ?>"><br>
    <input name="precio" value="<?= $producto->getPrecio() ?>"><br>

    <select name="iva">
        <option value="4">4%</option>
        <option value="10">10%</option>
        <option value="21">21%</option>
    </select><br><br>

    <button>Actualizar</button>
</form>