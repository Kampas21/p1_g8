<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';



$admin = require_role('gerente');

if (!is_post()) {
    redirect(RUTA_APP . '/vistas/usuarios/usuarios.php');
}
require_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash_set('error', 'ID de usuario no válido.');
    redirect(RUTA_APP . '/vistas/usuarios/usuarios.php');
}

if ($id === (int)$admin->getId()) {
    flash_set('error', 'No puedes desactivarte a ti mismo/a.');
    redirect(RUTA_APP . '/vistas/usuarios/usuarios.php');
}

$user = user_find_by_id($id);
if (!$user) {
    flash_set('error', 'Usuario no encontrado.');
    redirect(RUTA_APP . '/vistas/usuarios/usuarios.php');
}

if ($user->isActivo()) {
    flash_set('info', 'El usuario ya estaba desactivado.');
    redirect(RUTA_APP . '/vistas/usuarios/usuarios.php?ver=todo');     
}

user_soft_delete($id);
flash_set('success', 'Usuario desactivado (borrado lógico) correctamente.');
redirect(RUTA_APP . '/vistas/usuarios/usuarios.php?ver=todo');