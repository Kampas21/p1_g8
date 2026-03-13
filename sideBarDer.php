<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nombre = $_SESSION['nombre'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 'No autenticado';
?>

<h3>Panel usuario</h3>
<p><strong>Usuario:</strong> <?= htmlspecialchars($nombre) ?></p>
<p><strong>Rol:</strong> <?= htmlspecialchars($rol) ?></p>

<hr>

<h4>Accesos rápidos</h4>
<ul>
    <li><a href="/P1/p1_g8/vistas/usuarios/acceso.php#login">Iniciar sesión</a></li>
    <li><a href="/P1/p1_g8/vistas/usuarios/acceso.php#registro">Registrarse</a></li>
</ul>