<?php
require_once '../../includes/productoService.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    ProductoService::desactivar($id);
}

header("Location: categoriasList.php");
exit;
