<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';

$admin = require_role('gerente');
$modo = (string)($_GET['modo'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$isCreate = ($modo === 'crear') || ($id <= 0);
$user = null;

if (!$isCreate) {
    $user = user_find_by_id($id);
    if (!$user) {
        flash_set('error', 'Usuario no encontrado.');
        redirect('usuarios.php');
    }
}

$formValues = [
    'username' => $user['username'] ?? '',
    'email' => $user['email'] ?? '',
    'nombre' => $user['nombre'] ?? '',
    'apellidos' => $user['apellidos'] ?? '',
    'rol' => $user['rol'] ?? 'cliente',
    'activo' => isset($user['activo']) ? (int)$user['activo'] : 1,
];

$errors = [];

if (is_post()) {
    require_csrf();

    [$clean, $errors] = user_validate_data($_POST, $isCreate, $isCreate ? null : (int)$user['id'], true);
    $formValues = [
        'username' => $clean['username'],
        'email' => $clean['email'],
        'nombre' => $clean['nombre'],
        'apellidos' => $clean['apellidos'],
        'rol' => $clean['rol'],
        'activo' => isset($_POST['activo']) ? 1 : 0,
    ];

    $avatarChoice = null;
    if (!$errors) {
        try {
            $avatarChoice = resolve_avatar_choice_from_request($user ?? [], $isCreate);
        } catch (RuntimeException $ex) {
            $errors['avatar'] = $ex->getMessage();
        }
    }

    if (!$errors) {
        if ($isCreate) {
            $newId = user_create($clean, $avatarChoice);
            flash_set('success', 'Usuario creado correctamente.');
            redirect('usuario_ver.php?id=' . $newId);
        }

        user_update((int)$user['id'], $clean, ['avatar_choice' => $avatarChoice, 'allow_role' => true]);

        // Si el gerente ha marcado desactivar, se aplica como borrado lógico.
        if ($formValues['activo'] === 0 && (int)$user['activo'] === 1) {
            if ((int)$user['id'] === (int)$admin['id']) {
                flash_set('error', 'No puedes desactivarte a ti mismo/a desde la edición.');
            } else {
                user_soft_delete((int)$user['id']);
                flash_set('success', 'Usuario actualizado y desactivado.');
                redirect('usuario_ver.php?id=' . (int)$user['id']);
            }
        } elseif ($formValues['activo'] === 1 && (int)$user['activo'] === 0) {
            user_reactivate((int)$user['id']);
            flash_set('success', 'Usuario actualizado y reactivado.');
            redirect('usuario_ver.php?id=' . (int)$user['id']);
        } else {
            flash_set('success', 'Usuario actualizado correctamente.');
            redirect('usuario_ver.php?id=' . (int)$user['id']);
        }
    }
}

layout_header($isCreate ? 'Crear usuario' : 'Editar usuario', 'usuarios.php');
?>
<main>
  <div class="panel">
    <h2><?= $isCreate ? 'Creación de usuario' : 'Actualización de usuario' ?></h2>
    <?php layout_flash_messages(); ?>
    <p class="muted">
      <?= $isCreate ? 'Formulario para gerente: creación de usuarios del sistema.' : 'Formulario para gerente: edición de datos, rol y avatar.' ?>
    </p>
  </div>

  <section class="panel">
    <?php if ($errors): ?>
      <div class="notice error">Revisa los campos del formulario.</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="form-grid">
        <div>
          <label for="uf-username">Usuario</label>
          <input id="uf-username" type="text" name="username" value="<?= e($formValues['username']) ?>">
          <?php if (isset($errors['username'])): ?><div class="notice error"><?= e($errors['username']) ?></div><?php endif; ?>
        </div>

        <div>
          <label for="uf-email">Email</label>
          <input id="uf-email" type="email" name="email" value="<?= e($formValues['email']) ?>">
          <?php if (isset($errors['email'])): ?><div class="notice error"><?= e($errors['email']) ?></div><?php endif; ?>
        </div>

        <div>
          <label for="uf-nombre">Nombre</label>
          <input id="uf-nombre" type="text" name="nombre" value="<?= e($formValues['nombre']) ?>">
          <?php if (isset($errors['nombre'])): ?><div class="notice error"><?= e($errors['nombre']) ?></div><?php endif; ?>
        </div>

        <div>
          <label for="uf-apellidos">Apellidos</label>
          <input id="uf-apellidos" type="text" name="apellidos" value="<?= e($formValues['apellidos']) ?>">
          <?php if (isset($errors['apellidos'])): ?><div class="notice error"><?= e($errors['apellidos']) ?></div><?php endif; ?>
        </div>

        <div>
          <label for="uf-password"><?= $isCreate ? 'Contraseña' : 'Nueva contraseña (opcional)' ?></label>
          <input id="uf-password" type="password" name="password">
          <?php if (isset($errors['password'])): ?><div class="notice error"><?= e($errors['password']) ?></div><?php endif; ?>
        </div>

        <div>
          <label for="uf-password-confirm"><?= $isCreate ? 'Repetir contraseña' : 'Repetir nueva contraseña' ?></label>
          <input id="uf-password-confirm" type="password" name="password_confirm">
          <?php if (isset($errors['password_confirm'])): ?><div class="notice error"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
        </div>

        <div>
          <label for="uf-rol">Rol</label>
          <select id="uf-rol" name="rol">
            <?php foreach (valid_roles() as $rol): ?>
              <option value="<?= e($rol) ?>" <?= $formValues['rol'] === $rol ? 'selected' : '' ?>><?= e(role_label($rol)) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['rol'])): ?><div class="notice error"><?= e($errors['rol']) ?></div><?php endif; ?>
        </div>

        <?php if (!$isCreate): ?>
          <div>
            <label>Estado del usuario</label>
            <label><input type="checkbox" name="activo" value="1" <?= (int)$formValues['activo'] === 1 ? 'checked' : '' ?>> Activo</label>
            <div class="muted">Desmarcar equivale a borrado lógico (desactivar).</div>
          </div>
        <?php endif; ?>

        <div class="full">
         <label>Avatar</label>

  <div class="actions-inline">
    <?php if (!$isCreate): ?>
      <label><input type="radio" name="avatar_mode" value="keep" checked> Mantener actual</label>
    <?php endif; ?>

    <label>
      <input type="radio" name="avatar_mode" value="default" <?= $isCreate ? 'checked' : '' ?>>
      Por defecto
    </label>

    <label>
      <input type="radio" name="avatar_mode" value="preset">
      Predefinido
    </label>

    <label>
      <input type="radio" name="avatar_mode" value="upload">
      Subir foto
    </label>
  </div>

  <?php if (!$isCreate && $user): ?>
    <div id="avatar-current-box" class="panel avatar-panel-block" style="margin-top:10px;">
      <span class="muted">Avatar actual:</span><br>
      <img class="avatar" src="<?= e($user['avatar_url']) ?>" alt="Avatar actual">
    </div>
  <?php endif; ?>

  <div id="avatar-preset-box" class="panel hidden avatar-panel-block" style="margin-top:10px;">
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

  <div id="avatar-upload-box" class="panel hidden avatar-panel-block" style="margin-top:10px;">
    <label for="uf-avatar-upload">Subir imagen (JPG/PNG/WEBP/GIF, máx. 2MB)</label>
    <input id="uf-avatar-upload" type="file" name="avatar_upload" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
  </div>

  <?php if (isset($errors['avatar'])): ?>
    <div class="notice error"><?= e($errors['avatar']) ?></div>
  <?php endif; ?>
</div>
      </div>

      <div class="actions-inline" style="margin-top:14px;">
        <button class="primary" type="submit"><?= $isCreate ? 'Crear usuario' : 'Guardar cambios' ?></button>
        <a class="btn" href="<?= $isCreate ? '/p1_g8/entities/usuarios.php' : ('/p1_g8/vistas/usuarios/usuario_ver.php?id=' . (int)$user['id']) ?>">Cancelar</a>
      </div>
    </form>
  </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeInputs = document.querySelectorAll('input[name="avatar_mode"]');
    const currentBox = document.getElementById('avatar-current-box');
    const presetBox = document.getElementById('avatar-preset-box');
    const uploadBox = document.getElementById('avatar-upload-box');

    function updateAvatarAdminUI() {
        const selected = document.querySelector('input[name="avatar_mode"]:checked');
        const mode = selected ? selected.value : '';

        if (currentBox) currentBox.classList.add('hidden');
        if (presetBox) presetBox.classList.add('hidden');
        if (uploadBox) uploadBox.classList.add('hidden');

        if (mode === 'keep' && currentBox) {
            currentBox.classList.remove('hidden');
        }

        if (mode === 'preset' && presetBox) {
            presetBox.classList.remove('hidden');
        }

        if (mode === 'upload' && uploadBox) {
            uploadBox.classList.remove('hidden');
        }
    }

    modeInputs.forEach(function (input) {
        input.addEventListener('change', updateAvatarAdminUI);
    });

    updateAvatarAdminUI();
});
</script>