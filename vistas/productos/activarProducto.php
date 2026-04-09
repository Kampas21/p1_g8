<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';

// 🔒 SOLO POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Método no permitido");
}

$user = current_user();

// 🔒 SOLO GERENTE
if (!$user || !user_has_role($user, 'gerente')) {
    http_response_code(403);
    die("Acceso denegado");
}

// 🔒 VALIDAR INPUT
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);

if (!$id || !$categoria_id) {
    http_response_code(400);
    die("Datos inválidos");
}

// 💾 EJECUTAR ACCIÓN
$ok = ProductoService::activar($id);

// ❌ CONTROL ERROR
if (!$ok) {
    http_response_code(500);
    die("Error al activar el producto");
}

// 🔁 REDIRECCIÓN
header("Location: ../productos/mostrarProductosCategoria.php?id=" . $categoria_id);
exit;