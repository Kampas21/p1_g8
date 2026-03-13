<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';

$user = current_user();

$nombre = $user['nombre'] ?? 'Invitado';
$rol = $user['rol'] ?? 'No autenticado';
?>

<h3>Panel usuario</h3>
<p><strong>Usuario:</strong> <?= htmlspecialchars($nombre) ?></p>
<p><strong>Rol:</strong> <?= htmlspecialchars(ucfirst($rol)) ?></p>

<hr>

<h4>Accesos rápidos</h4>
<ul class="menu-izq">
    <?php if ($user): ?>
        <li><a href="<?= RUTA_APP ?>/vistas/usuarios/perfil.php">Mi perfil</a></li>
        <li><a href="<?= RUTA_APP ?>/vistas/usuarios/logout.php">Cerrar sesión</a></li>
    <?php else: ?>
        <li><a href="<?= RUTA_APP ?>/vistas/usuarios/acceso.php#login">Iniciar sesión</a></li>
        <li><a href="<?= RUTA_APP ?>/vistas/usuarios/acceso.php#registro">Registrarse</a></li>
    <?php endif; ?>
</ul>