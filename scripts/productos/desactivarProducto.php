<?php

require_once __DIR__ . '/../../includes/productoService.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);

if ($id) {
    ProductoService::desactivar($id);
}

header("Location: ../../vistas/productos/mostrarProductosCategoria.php?id=" . $categoria_id);
exit;