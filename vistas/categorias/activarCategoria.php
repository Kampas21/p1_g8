<?php


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    http_response_code(403);
    die('Acceso denegado');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    die('ID de categoría no válido.');
}

$ok = CategoriaService::activar($id);
if (!$ok) {
    http_response_code(500);
    die('Error al activar la categoría.');
}

header('Location: categoriasList.php');
exit;