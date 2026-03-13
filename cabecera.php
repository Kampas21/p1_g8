<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';

function mostrarSaludo(): string {
    $user = current_user();

    if ($user) {
        $nombre = $user['nombre'] ?? $user['username'] ?? 'Usuario';
        return "Bienvenido, " . htmlspecialchars($nombre) .
            " <a href='/P1/p1_g8/vistas/usuarios/logout.php'>(salir)</a>";
    }

    return "Usuario desconocido. <a href='/P1/p1_g8/vistas/usuarios/acceso.php#login'>Login</a>";
}
?>

<div class="cabecera-superior">
    <div class="cabecera-brand">
        <img src="/P1/p1_g8/img/logo_personalizado.png" alt="Logo Bistro FDI" class="logo-cabecera">
        <div>
            <h1>Bistro FDI</h1>
            <div class="saludo"><?= mostrarSaludo(); ?></div>
        </div>
    </div>

    <nav class="menu-principal">
        <ul>
            <li><a href="/P1/p1_g8/index.php">Inicio</a></li>
            <li><a href="/P1/p1_g8/detalles.php">Detalles</a></li>
            <li><a href="/P1/p1_g8/bocetos.php">Bocetos</a></li>
            <li><a href="/P1/p1_g8/miembros.php">Miembros</a></li>
            <li><a href="/P1/p1_g8/planificacion.php">Planificación</a></li>
            <li><a href="/P1/p1_g8/contacto.php">Contacto</a></li>
        </ul>
    </nav>
</div>