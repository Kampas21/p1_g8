<?php
function mostrarSaludo() {
    if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
        $nombre = $_SESSION['nombre'] ?? 'Usuario';
        return "Bienvenido, " . htmlspecialchars($nombre) . " <a href='/P1/p1_g8/logout.php'>(salir)</a>";
    }
    return "Usuario desconocido. <a href='/P1/p1_g8/login.php'>Login</a>";
}
?>
<header>
    <h1>Bistro FDI</h1>
    <div class="saludo"><?= mostrarSaludo(); ?></div>
</header>