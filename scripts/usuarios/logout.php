<?php
declare(strict_types=1);



require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

if (is_post()) {
    require_csrf();
}
logout_user();
flash_set('success', 'Sesión cerrada correctamente.');
redirect('../../index.php');