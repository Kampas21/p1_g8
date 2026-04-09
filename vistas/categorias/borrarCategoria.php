<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

// 🔒 SOLO POST (OBLIGATORIO)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

$user = current_user();

// 🔒 SOLO GERENTE
if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

// 🔒 VALIDAR INPUT
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("ID de categoría no válido.");
}

// 💾 EJECUTAR ACCIÓN
$ok = CategoriaService::desactivar($id);

// ❌ COMPROBAR RESULTADO (MEJORA IMPORTANTE)
if (!$ok) {
    die("Error al desactivar la categoría");
}

// 🔁 REDIRECCIÓN SEGURA
header("Location: categoriasList.php");
exit;