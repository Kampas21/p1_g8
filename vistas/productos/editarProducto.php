<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    die('Acceso no autorizado');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$id) {
    die('Producto no válido');
}

$producto = ProductoService::getById($id);

if (!$categoria_id) {
    $categoria_id = $producto->getCategoriaId();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $iva = $_POST['iva'] ?? '';
    $categoria_id_post = $_POST['categoria_id'] ?? $categoria_id;

    ProductoService::actualizar($id, $nombre, $descripcion, $categoria_id_post, $precio, $iva);

    header('Location: mostrarProductosCategoria.php?id=' . $categoria_id_post);
    exit;
}

$tituloPagina = 'Editar producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Editar producto</h1>

<form method="POST">
    <input type="hidden" name="categoria_id" value="<?= $categoria_id ?>">

    <input type="text" name="nombre" value="<?= htmlspecialchars($producto->getNombre()) ?>" required><br><br>

    <textarea name="descripcion" required><?= htmlspecialchars($producto->getDescripcion()) ?></textarea><br><br>

    <input type="number" step="0.01" name="precio" value="<?= $producto->getPrecio() ?>" required><br><br>

    <select name="iva">
        <option value="4" <?= $producto->getIVA() == 4 ? 'selected' : '' ?>>4%</option>
        <option value="10" <?= $producto->getIVA() == 10 ? 'selected' : '' ?>>10%</option>
        <option value="21" <?= $producto->getIVA() == 21 ? 'selected' : '' ?>>21%</option>
    </select><br><br>

    <button type="submit">Actualizar</button>
</form>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';