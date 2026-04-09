<?php

require_once __DIR__ . '/../../includes/productoService.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);

if (!$id) {
    die("ID inválido");
}

ProductoService::activar($id);

if ($categoria_id) {
    header("Location: mostrarProductosCategoria.php?id=" . $categoria_id);
    exit;
}

header("Location: ../categorias/categoriasList.php");
exit;