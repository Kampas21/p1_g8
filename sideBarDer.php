<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/util.php';

$user = current_user();

$nombre = $user ? $user->getNombre() : 'Invitado';
$rol = $user ? $user->getRol() : 'No autenticado';
$avatarSrc = $user ? $user->getAvatarUrl() : '';
?>

<section class="panel-usuario-sidebar">
    <h3>Panel usuario</h3>

    <p><strong>Usuario:</strong> <?= escaparHtml($nombre) ?></p>
    <p><strong>Rol:</strong> <?= escaparHtml(ucfirst($rol)) ?></p>

    <?php if ($user): ?>
        <div class="sidebar-avatar-box">
            <img src="<?= escaparHtml($avatarSrc) ?>"
                 alt="Foto de perfil de <?= escaparHtml($nombre) ?>"
                 class="sidebar-avatar">
        </div>
    <?php endif; ?>

    <hr>

    <nav aria-label="Accesos rápidos del usuario">
        <h4>Accesos rápidos</h4>

        <ul class="menu-izq">
            <?php if ($user && $user->getRol() === 'gerente'): ?>

                <li><a href="<?= RUTA_APP ?>/vistas/usuarios/usuarios.php">Listado de usuarios</a></li>
                <li><a href="<?= RUTA_APP ?>/vistas/usuarios/usuario_form.php?modo=crear">Registrar trabajador</a></li>
                <li><a href="<?= RUTA_APP ?>/vistas/usuarios/perfil.php">Mi perfil</a></li>
                <li><a href="<?= RUTA_APP ?>/scripts/usuarios/logout.php">Cerrar sesión</a></li>

            <?php elseif ($user): ?>

                <li><a href="<?= RUTA_APP ?>/vistas/usuarios/perfil.php">Mi perfil</a></li>
                <li><a href="<?= RUTA_APP ?>/scripts/usuarios/logout.php">Cerrar sesión</a></li>

            <?php else: ?>

                <li><a href="<?= RUTA_APP ?>/vistas/usuarios/acceso.php#login">Iniciar sesión</a></li>
                <li><a href="<?= RUTA_APP ?>/vistas/usuarios/registro.php">Registrarse</a></li>

            <?php endif; ?>
        </ul>
    </nav>
</section>