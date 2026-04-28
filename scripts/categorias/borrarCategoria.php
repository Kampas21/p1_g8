<?php


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/CategoriaDAO.php';

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

$ok = CategoriaDAO::desactivar($id);
if (!$ok) {
    http_response_code(500);
    die('Error al desactivar la categoría.');
}

header('Location: ../../vistas/categorias/categoriasList.php');
exit;