<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';

$user = current_user();

// 🔒 Solo gerente
if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id) {
    ProductoService::desactivar($id);
}

// Redirigir
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;