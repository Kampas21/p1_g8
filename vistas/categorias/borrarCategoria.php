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

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">


<h1>Desactivar categoría</h1>

<p>
    ¿Seguro que quieres desactivar la categoría
    <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>?
</p>

<p>
    Sus productos pasarán a no ofertados.
</p>

<form method="POST">
<button type="submit" class="btn-aceptar">Sí, desactivar</button>
</form>

<p>
    <a class="btn-volver" href="categoriasList.php">Cancelar</a>
</p>