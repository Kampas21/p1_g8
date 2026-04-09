<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/productoService.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id) {
    ProductoService::desactivar($id);
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();