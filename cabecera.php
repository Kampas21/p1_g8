<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function mostrarSaludo() {
    if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
        $nombre = $_SESSION['nombre'] ?? 'Usuario';
        return 'Bienvenido, ' . htmlspecialchars($nombre) .
               ' <a href="/P1/p1_g8/vistas/usuarios/logout.php">(salir)</a>';
    }

    return 'Usuario desconocido. <a href="/P1/p1_g8/vistas/usuarios/acceso.php#login">Login</a>';
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
            <li><a href="/P1/p1_g8/detalles.html">Detalles</a></li>
            <li><a href="/P1/p1_g8/bocetos.html">Bocetos</a></li>
            <li><a href="/P1/p1_g8/miembros.html">Miembros</a></li>
            <li><a href="/P1/p1_g8/planificacion.html">Planificación</a></li>
            <li><a href="/P1/p1_g8/contacto.html">Contacto</a></li>
        </ul>
    </nav>
</div>