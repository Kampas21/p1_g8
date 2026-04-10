<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';

function mostrarSaludo(): string {
    $user = current_user();

    if ($user) {
        // Ahora usamos los getters del objeto
        $nombre = $user->getNombre() ?: $user->getUsername();
        return "Bienvenido, " . htmlspecialchars($nombre) .
            " <a href='" . RUTA_APP . "/vistas/usuarios/logout.php'>(salir)</a>";
    }

    return "Usuario desconocido. <a href='" . RUTA_APP . "/vistas/usuarios/acceso.php#login'>Login</a>";
}
?>

<div class="cabecera-superior">
    <div class="cabecera-brand">
        <img src="<?= RUTA_APP ?>/img/logo_personalizado.png" alt="Logo Bistro FDI" class="logo-cabecera">
        <div>
            <h1>Bistro FDI</h1>
            <div class="saludo"><?= mostrarSaludo(); ?></div>
        </div>
    </div>

    <nav class="menu-principal">
        <ul>
            <?php if (current_user()): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/pedidos/carrito.php">🛒 Mi Carrito</a></li>
            <?php endif; ?>
            <li><a href="<?= RUTA_APP ?>/miembros.php">👥 Miembros</a></li>
            <li><a href="<?= RUTA_APP ?>/contacto.php">✉️ Contacto</a></li>
        </ul>
    </nav>
</div>