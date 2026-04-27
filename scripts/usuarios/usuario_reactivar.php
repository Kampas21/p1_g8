<?php
declare(strict_types=1);



require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/auth.php';

$admin = require_role('gerente');


if (!is_post()) {
    redirect(RUTA_APP . '/vistas/usuarios/usuarios.php');
}
require_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    UsuarioDAO::user_reactivate($id);
    flash_set('success', 'Usuario reactivado correctamente.');
} else {
    flash_set('error', 'ID de usuario no válido.');
}

redirect(RUTA_APP . '/vistas/usuarios/usuarios.php?ver=todo');