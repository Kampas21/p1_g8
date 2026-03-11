<?php
require_once __DIR__ . '/../../entities/categoria.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID no válido");
}

Categoria::activarCategoria((int)$id);

header("Location: categoriasList.php");
exit();