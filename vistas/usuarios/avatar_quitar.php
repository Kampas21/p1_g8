<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_login();

if (!is_post()) {
    redirect('perfil.php');
}
require_csrf();

user_remove_custom_avatar((int)$user['id']);
flash_set('success', 'Avatar personalizado eliminado. Se ha restaurado el avatar por defecto.');
redirect('perfil.php');