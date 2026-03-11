<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    require_csrf();
}
logout_user();
flash_set('success', 'Sesión cerrada correctamente.');
redirect('acceso.php');
