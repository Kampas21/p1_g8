<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

if (current_user()) {
    redirect('perfil.php');
}

$loginInput = ['login' => ''];
$loginErrors = [];

if (is_post()) {
    require_csrf();

    $loginInput['login'] = trim((string)($_POST['login'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($loginInput['login'] === '') {
        $loginErrors['login'] = 'Introduce tu usuario o email.';
    }

    if ($password === '') {
        $loginErrors['password'] = 'Introduce tu contraseña.';
    }

    if (!$loginErrors) {
        $user = user_find_by_username_or_email($loginInput['login']);

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            $loginErrors['general'] = 'Credenciales incorrectas.';
        } else {
            login_user($user);
            redirect('perfil.php');
        }
    }
}

$tituloPagina = 'Iniciar sesión | Bistro FDI';

ob_start();
?>

<div class="panel">
<h2>Iniciar sesión</h2>


<?php foreach (flash_get_all() as $f): ?>
    <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach; ?>


<?php if (isset($loginErrors['general'])): ?>
<div class="notice error"><?= e($loginErrors['general']) ?></div>
<?php endif; ?>

<form method="post">

<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

<label>Usuario o Email</label>
<input type="text" name="login" value="<?= e($loginInput['login']) ?>">

<label>Contraseña</label>
<input type="password" name="password">

<button type="submit">Entrar</button>

</form>

<p>
¿No tienes cuenta?
<a href="registro.php">Registrarte</a>
</p>

</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>