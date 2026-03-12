<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';

$user = require_login();

$errors = [];
$formValues = [
    'username' => $user['username'],
    'email' => $user['email'],
    'nombre' => $user['nombre'],
    'apellidos' => $user['apellidos'],
];

if (is_post()) {
    require_csrf();
    $accion = (string)($_POST['accion'] ?? '');

    if ($accion === 'quitar_avatar_personalizado') {
        user_remove_custom_avatar((int)$user['id']);
        flash_set('success', 'Avatar personalizado eliminado. Se ha restaurado el avatar por defecto.');
        redirect('perfil.php');
    }

    if ($accion === 'guardar_perfil') {
        [$clean, $errors] = user_validate_data($_POST, false, (int)$user['id'], false);

        $formValues = [
            'username' => $clean['username'],
            'email' => $clean['email'],
            'nombre' => $clean['nombre'],
            'apellidos' => $clean['apellidos'],
        ];

        $avatarChoice = null;

        if (!$errors) {
            try {
                $avatarChoice = resolve_avatar_choice_from_request($user, false);
            } catch (RuntimeException $ex) {
                $errors['avatar'] = $ex->getMessage();
            }
        }

        if (!$errors) {
            user_update((int)$user['id'], $clean, [
                'avatar_choice' => $avatarChoice,
                'allow_role' => false
            ]);

            flash_set('success', 'Perfil actualizado correctamente.');
            redirect('perfil.php');
        }
    }
}

$user = current_user() ?? $user;

/* =========================
   PEDIDOS DEL USUARIO
   ========================= */

$pedidosActivos = [];
$pedidosHistorico = [];
$pedidosDisponibles = false;

$conn = crearConexion();

/* Comprobar si existe la tabla pedidos */
$checkTable = $conn->query("SHOW TABLES LIKE 'pedidos'");
if ($checkTable && $checkTable->num_rows > 0) {
    $pedidosDisponibles = true;
}

/* Cargar pedidos si la tabla existe */
if ($pedidosDisponibles) {
    $uid = (int)$user['id'];

    $sqlActivos = "
        SELECT numero_pedido, estado, fecha_hora, total
        FROM pedidos
        WHERE cliente_id = ?
          AND estado IN ('En preparación', 'Cocinando', 'Listo cocina', 'Terminado')
        ORDER BY fecha_hora DESC
    ";
    $stmtAct = $conn->prepare($sqlActivos);
    $stmtAct->bind_param("i", $uid);
    $stmtAct->execute();
    $resultAct = $stmtAct->get_result();

    while ($row = $resultAct->fetch_assoc()) {
        $pedidosActivos[] = $row;
    }

    $stmtAct->close();

    $sqlHist = "
        SELECT numero_pedido, fecha_hora, tipo, total, estado
        FROM pedidos
        WHERE cliente_id = ?
        ORDER BY fecha_hora DESC
        LIMIT 15
    ";
    $stmtHist = $conn->prepare($sqlHist);
    $stmtHist->bind_param("i", $uid);
    $stmtHist->execute();
    $resultHist = $stmtHist->get_result();

    while ($row = $resultHist->fetch_assoc()) {
        $pedidosHistorico[] = $row;
    }

    $stmtHist->close();
}

$conn->close();

layout_header('Perfil', 'perfil.php');
?>
<main>
  <div class="panel">
    <h2>Perfil y pedidos</h2>
    <?php layout_flash_messages(); ?>
  </div>

  <div class="profile-layout">
    <section class="panel profile-card">
      <h3>Avatar + datos de usuario</h3>

      <div class="profile-top">
        <img class="avatar lg" src="<?= e($user['avatar_url']) ?>" alt="Avatar de <?= e($user['username']) ?>">
        <div>
          <p><strong>Usuario:</strong> <?= e($user['username']) ?></p>
          <p><strong>Email:</strong> <?= e($user['email']) ?></p>
          <p><strong>Nombre y apellidos:</strong> <?= e($user['nombre']) ?> <?= e($user['apellidos']) ?></p>
          <p><strong>Rol:</strong> <?= e(role_label((string)$user['rol'])) ?></p>
        </div>
      </div>

      <details style="margin-top:16px;" open>
        <summary><strong>Editar perfil</strong></summary>

        <?php if ($errors): ?>
          <div class="notice error">Revisa los errores del formulario.</div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="accion" value="guardar_perfil">

          <div class="form-grid" style="margin-top:12px;">
            <div>
              <label for="pf-username">Usuario</label>
              <input id="pf-username" type="text" name="username" value="<?= e($formValues['username']) ?>">
              <?php if (isset($errors['username'])): ?><div class="notice error"><?= e($errors['username']) ?></div><?php endif; ?>
            </div>

            <div>
              <label for="pf-email">Email</label>
              <input id="pf-email" type="email" name="email" value="<?= e($formValues['email']) ?>">
              <?php if (isset($errors['email'])): ?><div class="notice error"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>

            <div>
              <label for="pf-nombre">Nombre</label>
              <input id="pf-nombre" type="text" name="nombre" value="<?= e($formValues['nombre']) ?>">
              <?php if (isset($errors['nombre'])): ?><div class="notice error"><?= e($errors['nombre']) ?></div><?php endif; ?>
            </div>

            <div>
              <label for="pf-apellidos">Apellidos</label>
              <input id="pf-apellidos" type="text" name="apellidos" value="<?= e($formValues['apellidos']) ?>">
              <?php if (isset($errors['apellidos'])): ?><div class="notice error"><?= e($errors['apellidos']) ?></div><?php endif; ?>
            </div>

            <div>
              <label for="pf-password">Nueva contraseña (opcional)</label>
              <input id="pf-password" type="password" name="password">
              <?php if (isset($errors['password'])): ?><div class="notice error"><?= e($errors['password']) ?></div><?php endif; ?>
            </div>

            <div>
              <label for="pf-password-confirm">Repetir nueva contraseña</label>
              <input id="pf-password-confirm" type="password" name="password_confirm">
              <?php if (isset($errors['password_confirm'])): ?><div class="notice error"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
            </div>

            <div class="full">
  <label>Avatar</label>

  <div class="actions-inline">
    <label><input type="radio" name="avatar_mode" value="keep" checked> Mantener actual</label>
    <label><input type="radio" name="avatar_mode" value="default"> Usar por defecto</label>
    <label><input type="radio" name="avatar_mode" value="preset"> Usar predefinido</label>
    <label><input type="radio" name="avatar_mode" value="upload"> Subir foto</label>
  </div>

  <div id="avatar-current-box" class="panel avatar-panel-block" style="margin-top:10px;">
    <div class="muted">Avatar actual:</div>
    <img class="avatar" src="<?= e($user['avatar_url']) ?>" alt="Avatar actual">

    <?php if (($user['avatar_tipo'] ?? '') === 'custom'): ?>
      <div style="margin-top:10px;">
        <button class="btn small" type="submit" name="accion" value="quitar_avatar_personalizado">
          Eliminar avatar personalizado (volver a por defecto)
        </button>
      </div>
    <?php endif; ?>
  </div>

  <div id="avatar-preset-box" class="panel hidden avatar-panel-block" style="margin-top:10px;">
    <div class="avatar-option-grid" style="margin-top:12px;">
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
    <label for="pf-avatar-upload">Subir nueva imagen</label>
    <input id="pf-avatar-upload" type="file" name="avatar_upload" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
  </div>

  <?php if (isset($errors['avatar'])): ?>
    <div class="notice error"><?= e($errors['avatar']) ?></div>
  <?php endif; ?>
</div>
          </div>

          <div class="actions-inline" style="margin-top:12px;">
            <button class="primary" type="submit">Guardar cambios</button>
            <?php if (user_has_role($user, 'gerente')): ?>
              <a class="btn" href="../../entities/usuarios.php">Ir a gestión de usuarios</a>
            <?php endif; ?>
          </div>
        </form>
      </details>
    </section>

    <section class="panel progress-card">
      <h3>Pedidos activos</h3>
      <p class="muted">Estados relevantes: En preparación / Cocinando / Listo cocina / Terminado.</p>

      <?php if (!$pedidosDisponibles): ?>
        <div class="pedido-linea">
          <strong>Ejemplo visual (boceto)</strong>
          <div class="progress-steps">
            <div class="progress-step done"><div class="dot"></div>En preparación</div>
            <div class="progress-step done"><div class="dot"></div>Cocinando</div>
            <div class="progress-step active"><div class="dot"></div>Listo cocina</div>
            <div class="progress-step"><div class="dot"></div>Terminado</div>
          </div>
        </div>
      <?php elseif (!$pedidosActivos): ?>
        <p>No tienes pedidos activos en este momento.</p>
      <?php else: ?>
        <?php foreach ($pedidosActivos as $p): ?>
          <div class="pedido-linea">
            <strong>Pedido #<?= e((string)$p['numero_pedido']) ?></strong>
            <div class="muted">Estado actual: <?= e((string)$p['estado']) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>

  <section class="panel">
    <h3>Histórico de pedidos</h3>

    <?php if (!$pedidosDisponibles): ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th></tr>
          </thead>
          <tbody>
            <tr><td colspan="5" class="muted">Sin datos reales todavía.</td></tr>
          </tbody>
        </table>
      </div>

    <?php elseif (!$pedidosHistorico): ?>
      <p>No hay pedidos registrados todavía.</p>

    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th></tr>
          </thead>
          <tbody>
            <?php foreach ($pedidosHistorico as $p): ?>
              <tr>
                <td><?= e((string)$p['numero_pedido']) ?></td>
                <td><?= e((string)$p['fecha_hora']) ?></td>
                <td><?= e((string)$p['tipo']) ?></td>
                <td><?= e((string)$p['total']) ?></td>
                <td><?= e((string)$p['estado']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modeInputs = document.querySelectorAll('input[name="avatar_mode"]');
    const currentBox = document.getElementById('avatar-current-box');
    const presetBox = document.getElementById('avatar-preset-box');
    const uploadBox = document.getElementById('avatar-upload-box');

    function updateAvatarProfileUI() {
        const selected = document.querySelector('input[name="avatar_mode"]:checked');
        const mode = selected ? selected.value : 'keep';

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
        input.addEventListener('change', updateAvatarProfileUI);
    });

    updateAvatarProfileUI();
});
</script>