<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

$registerInput = [
'username' => '',
'email' => '',
'nombre' => '',
'apellidos' => '',
];

$registerErrors = [];

if (is_post()) {

require_csrf();

[$clean, $registerErrors] = user_validate_data($_POST, true, null, false);

$registerInput = [
'username' => $clean['username'],
'email' => $clean['email'],
'nombre' => $clean['nombre'],
'apellidos' => $clean['apellidos'],
];

if (!$registerErrors) {

$clean['rol'] = 'cliente';

$newId = user_create($clean, ['type' => 'default']);

$user = user_find_by_id($newId);

login_user($user);

redirect('perfil.php');

}

}

$tituloPagina = 'Registro | Bistro FDI';

ob_start();
?>

<div class="panel">
<h2>Registro</h2>

<form method="post">

<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

<label>Usuario</label>
<input type="text" name="username" value="<?= e($registerInput['username']) ?>">

<label>Email</label>
<input type="email" name="email" value="<?= e($registerInput['email']) ?>">

<label>Nombre</label>
<input type="text" name="nombre" value="<?= e($registerInput['nombre']) ?>">

<label>Apellidos</label>
<input type="text" name="apellidos" value="<?= e($registerInput['apellidos']) ?>">

<label>Contraseña</label>
<input type="password" name="password">

<label>Repetir contraseña</label>
<input type="password" name="password_confirm">

<button type="submit">Registrarse</button>

</form>

<p>
¿Ya tienes cuenta?
<a href="acceso.php">Iniciar sesión</a>
</p>

</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';