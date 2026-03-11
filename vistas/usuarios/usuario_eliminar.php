<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$admin = require_role('gerente');

if (!is_post()) {
    redirect('usuarios.php');
}
require_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'ID de usuario no válido.');
    redirect('usuarios.php');
}

if ($id === (int)$admin['id']) {
    flash_set('error', 'No puedes desactivarte a ti mismo/a.');
    redirect('usuarios.php');
}

$user = user_find_by_id($id);
if (!$user) {
    flash_set('error', 'Usuario no encontrado.');
    redirect('usuarios.php');
}

if ((int)$user['activo'] !== 1) {
    flash_set('info', 'El usuario ya estaba desactivado.');
    redirect('usuarios.php?ver=todo');
}

user_soft_delete($id);
flash_set('success', 'Usuario desactivado (borrado lógico) correctamente.');
redirect('usuarios.php?ver=todo');
