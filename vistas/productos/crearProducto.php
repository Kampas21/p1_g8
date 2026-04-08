<?php
require_once '../../includes/productoService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = filter_input(INPUT_POST, 'nombre');
    $descripcion = filter_input(INPUT_POST, 'descripcion');
    $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);

    ProductoService::crear($nombre, $descripcion, $categoria_id, $precio, $iva);

    header("Location: categoriasList.php");
    exit;
}
?>

<h2>Crear producto</h2>

<form method="POST">
    <input name="nombre" placeholder="Nombre" required><br>
    <textarea name="descripcion"></textarea><br>
    <input name="categoria_id" placeholder="ID categoría" required><br>
    <input name="precio" type="number" step="0.01" required><br>

    <select name="iva">
        <option value="4">4%</option>
        <option value="10">10%</option>
        <option value="21">21%</option>
    </select><br><br>

    <button>Crear</button>
</form>