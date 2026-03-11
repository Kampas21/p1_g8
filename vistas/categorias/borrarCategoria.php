<?php 
require_once __DIR__ . '/../../entities/categoria.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID de categoría no válido.");
}

$categoria = Categoria::getCategoriaById((int)$id);

if (!$categoria) {
    die("La categoría no existe.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Categoria::borrarCategoria((int)$id);
    header('Location: categoriasList.php');
    exit();
}
?>

<h1>Desactivar categoría</h1>

<p>
    ¿Seguro que quieres desactivar la categoría 
    <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>?
</p>

<p>
    Esto pondrá como no ofertados todos los productos de esta categoría.
</p>

<form method="POST">
    <button type="submit">Sí, desactivar</button>
</form>

<p>
    <a href="categoriasList.php">Cancelar</a>
</p>