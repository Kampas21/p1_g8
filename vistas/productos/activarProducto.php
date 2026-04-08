<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

// 🔒 Control de acceso
$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso no autorizado");
}

// 📥 Recoger datos
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$id || !$categoria_id) {
    die("Datos inválidos");
}

// 🔄 Activar producto (ofertado = 1)
$stmt = $conn->prepare("UPDATE productos SET ofertado = 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 🔙 Volver a la categoría
header("Location: mostrarProductosCategoria.php?id=" . $categoria_id);
exit;