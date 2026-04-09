<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/categoriaService.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

$user = current_user();


if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("ID de categoría no válido.");
}


$ok = CategoriaService::desactivar($id);


if (!$ok) {
    die("Error al desactivar la categoría");
}


header("Location: categoriasList.php");
exit;