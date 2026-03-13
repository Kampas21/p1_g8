<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

if (is_post()) {
    require_csrf();
}
logout_user();
flash_set('success', 'Sesión cerrada correctamente.');
redirect('acceso.php');