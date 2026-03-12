<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';

$admin = require_role('gerente');

if (!is_post()) {
    redirect('/p1_g8/entities/usuarios.php');
}
require_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'ID de usuario no válido.');
    redirect('/p1_g8/entities/usuarios.php');
}

if ($id === (int)$admin['id']) {
    flash_set('error', 'No puedes desactivarte a ti mismo/a.');
    redirect('/p1_g8/entities/usuarios.php');
}

$user = user_find_by_id($id);
if (!$user) {
    flash_set('error', 'Usuario no encontrado.');
    redirect('/p1_g8/entities/usuarios.php');
}

if ((int)$user['activo'] !== 1) {
    flash_set('info', 'El usuario ya estaba desactivado.');
    redirect('/p1_g8/entities/usuarios.php?ver=todo');
}

user_soft_delete($id);
flash_set('success', 'Usuario desactivado (borrado lógico) correctamente.');
redirect('/p1_g8/entities/usuarios.php?ver=todo');
