<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

logout_user();

flash_set('success', 'Sesión cerrada correctamente.');

redirect(RUTA_APP . '/index.php');