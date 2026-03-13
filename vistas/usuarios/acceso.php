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
$registerInput = [
    'username' => '',
    'email' => '',
    'nombre' => '',
    'apellidos' => '',
];
$loginErrors = [];
$registerErrors = [];
$activeTab = 'login';

if (is_post()) {
    require_csrf();
    $accion = (string)($_POST['accion'] ?? '');

    if ($accion === 'login') {
        $activeTab = 'login';
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
                flash_set('success', 'Sesión iniciada correctamente. Bienvenido/a, ' . $user['nombre'] . '.');
                redirect('perfil.php');
            }
        }
    }

    if ($accion === 'registro') {
        $activeTab = 'registro';

        [$clean, $registerErrors] = user_validate_data($_POST, true, null, false);
        $registerInput = [
            'username' => $clean['username'],
            'email' => $clean['email'],
            'nombre' => $clean['nombre'],
            'apellidos' => $clean['apellidos'],
        ];

        $avatarChoice = ['type' => 'default', 'value' => null];

        if (!$registerErrors) {
            try {
                $avatarChoice = resolve_avatar_choice_from_request(null, true);
            } catch (RuntimeException $ex) {
                $registerErrors['avatar'] = $ex->getMessage();
            }
        }

        if (!$registerErrors) {
            $clean['rol'] = 'cliente';
            $newId = user_create($clean, $avatarChoice);
            $user = user_find_by_id($newId);

            if ($user) {
                login_user($user);
            }

            flash_set('success', 'Registro completado. Tu usuario se ha creado con rol cliente.');
            redirect('perfil.php');
        }
    }
}

$tituloPagina = 'Acceso | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<div class="panel">
    <h2>Login / Registro</h2>
    <?php
    foreach (flash_get_all() as $item) {
        $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info';
        echo '<div class="notice ' . e($type) . '">' . e($item['message']) . '</div>';
    }
    ?>
</div>

<div class="auth-layout">
    <section id="login" class="auth-card">
        <h2>Formulario Login</h2>
        <p class="muted">Usuario/email + contraseña.</p>

        <?php if (isset($loginErrors['general'])): ?>
            <div class="notice error"><?= e($loginErrors['general']) ?></div>
        <?php endif; ?>

        <form action="acceso.php#login" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="accion" value="login">

            <div class="form-grid">
                <div class="full">
                    <label for="login-login">Usuario / Email</label>
                    <input id="login-login" type="text" name="login" value="<?= e($loginInput['login']) ?>">
                    <?php if (isset($loginErrors['login'])): ?>
                        <div class="notice error"><?= e($loginErrors['login']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="full">
                    <label for="login-password">Contraseña</label>
                    <input id="login-password" type="password" name="password">
                    <?php if (isset($loginErrors['password'])): ?>
                        <div class="notice error"><?= e($loginErrors['password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="auth-actions actions-inline">
                <button class="primary" type="submit">Entrar</button>
                <a class="btn" href="#registro">Ir a registro</a>
            </div>
        </form>
    </section>

    <section id="registro" class="auth-card">
        <h2>Formulario Registro (cliente)</h2>
        <p class="muted">Se crea con rol <strong>cliente</strong> por defecto.</p>

        <?php if ($registerErrors): ?>
            <div class="notice error">Revisa los campos marcados en el formulario de registro.</div>
        <?php endif; ?>

        <form action="acceso.php#registro" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="accion" value="registro">

            <div class="form-grid">
                <div>
                    <label for="reg-username">Usuario</label>
                    <input id="reg-username" type="text" name="username" value="<?= e($registerInput['username']) ?>">
                    <?php if (isset($registerErrors['username'])): ?>
                        <div class="notice error"><?= e($registerErrors['username']) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="reg-email">Email</label>
                    <input id="reg-email" type="email" name="email" value="<?= e($registerInput['email']) ?>">
                    <?php if (isset($registerErrors['email'])): ?>
                        <div class="notice error"><?= e($registerErrors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="reg-nombre">Nombre</label>
                    <input id="reg-nombre" type="text" name="nombre" value="<?= e($registerInput['nombre']) ?>">
                    <?php if (isset($registerErrors['nombre'])): ?>
                        <div class="notice error"><?= e($registerErrors['nombre']) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="reg-apellidos">Apellidos</label>
                    <input id="reg-apellidos" type="text" name="apellidos" value="<?= e($registerInput['apellidos']) ?>">
                    <?php if (isset($registerErrors['apellidos'])): ?>
                        <div class="notice error"><?= e($registerErrors['apellidos']) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="reg-password">Contraseña</label>
                    <input id="reg-password" type="password" name="password">
                    <?php if (isset($registerErrors['password'])): ?>
                        <div class="notice error"><?= e($registerErrors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="reg-password-confirm">Repetir contraseña</label>
                    <input id="reg-password-confirm" type="password" name="password_confirm">
                    <?php if (isset($registerErrors['password_confirm'])): ?>
                        <div class="notice error"><?= e($registerErrors['password_confirm']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="full">
                    <label>Avatar</label>

                    <div class="actions-inline" style="margin-bottom: 8px;">
                        <label><input type="radio" name="avatar_mode" value="default" checked> Por defecto</label>
                        <label><input type="radio" name="avatar_mode" value="preset"> Seleccionar predefinido</label>
                        <label><input type="radio" name="avatar_mode" value="upload"> Subir foto</label>
                    </div>

                    <div id="avatar-preset-box" class="panel hidden avatar-panel-block" style="margin:0;">
                        <p class="muted" style="margin-top:0;">Avatares predefinidos</p>
                        <div class="avatar-option-grid">
                            <?php foreach (avatar_presets() as $key => $preset): ?>
                                <label class="avatar-option">
                                    <input type="radio" name="avatar_preset" value="<?= e($key) ?>">
                                    <img src="<?= e($preset['path']) ?>" alt="<?= e($preset['label']) ?>">
                                    <div><?= e($preset['label']) ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div id="avatar-upload-box" class="panel hidden avatar-panel-block" style="margin-top:12px;">
                        <label for="reg-avatar-upload">Subir imagen (JPG/PNG/WEBP/GIF, máx. 2MB)</label>
                        <input id="reg-avatar-upload" type="file" name="avatar_upload" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                    </div>

                    <?php if (isset($registerErrors['avatar'])): ?>
                        <div class="notice error"><?= e($registerErrors['avatar']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="auth-actions actions-inline">
                <button class="primary" type="submit">Registrarme</button>
                <a class="btn" href="#login">Ya tengo cuenta</a>
            </div>
        </form>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeInputs = document.querySelectorAll('input[name="avatar_mode"]');
    const presetBox = document.getElementById('avatar-preset-box');
    const uploadBox = document.getElementById('avatar-upload-box');

    function updateAvatarRegisterUI() {
        const selected = document.querySelector('input[name="avatar_mode"]:checked');
        const mode = selected ? selected.value : 'default';

        if (presetBox) presetBox.classList.add('hidden');
        if (uploadBox) uploadBox.classList.add('hidden');

        if (mode === 'preset' && presetBox) {
            presetBox.classList.remove('hidden');
        }

        if (mode === 'upload' && uploadBox) {
            uploadBox.classList.remove('hidden');
        }
    }

    modeInputs.forEach(function (input) {
        input.addEventListener('change', updateAvatarRegisterUI);
    });

    updateAvatarRegisterUI();
});
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>
