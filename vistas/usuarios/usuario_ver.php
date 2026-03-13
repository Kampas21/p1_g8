<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

$admin = require_role('gerente');
$id = (int)($_GET['id'] ?? 0);
$user = $id > 0 ? user_find_by_id($id) : null;

if (!$user) {
    flash_set('error', 'Usuario no encontrado.');
    redirect(RUTA_APP . '/entities/usuarios.php');
}

$tituloPagina = 'Ver usuario';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>
  <div class="panel">
    <h2>Visualización de usuario</h2>
    
    <?php
    
    foreach (flash_get_all() as $item) {
        $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info';
        echo '<div class="notice ' . e($type) . '">' . e($item['message']) . '</div>';
    }
    ?>

    <p class="muted">Vista de detalle (listar / visualizar)</p>
  </div>

  <section class="panel">
    <div class="profile-top">
      <img class="avatar lg" src="<?= e($user['avatar_url']) ?>" alt="Avatar de <?= e($user['username']) ?>">
      <div>
        <dl>
          <p><strong>ID:</strong> <?= (int)$user['id'] ?></p>
          <p><strong>Usuario:</strong> <?= e($user['username']) ?></p>
          <p><strong>Email:</strong> <?= e($user['email']) ?></p>
          <p><strong>Nombre:</strong> <?= e($user['nombre']) ?></p>
          <p><strong>Apellidos:</strong> <?= e($user['apellidos']) ?></p>
          <p><strong>Rol:</strong> <?= e(role_label((string)$user['rol'])) ?></p>
          <p><strong>Estado:</strong> <?= (int)$user['activo'] === 1 ? 'Activo' : 'Inactivo' ?></p>
          <p><strong>Actualizado:</strong> <?= e((string)$user['updated_at']) ?></p>
        </dl>
      </div>
    </div>

    <div class="actions-inline" style="margin-top:14px;">
      <a class="btn" href="<?= RUTA_APP ?>/entities/usuarios.php">Volver al listado</a>
      <a class="btn primary" href="<?= RUTA_APP ?>/vistas/usuarios/usuario_form.php?id=<?= (int)$user['id'] ?>">Editar usuario</a>

      <?php if ((int)$user['activo'] === 1): ?>
        <form method="post" action="<?= RUTA_APP ?>/vistas/usuarios/usuario_eliminar.php" onsubmit="return confirm('¿Desactivar este usuario?');" style="display:inline;">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
          <button class="danger" type="submit" <?= (int)$user['id'] === (int)$admin['id'] ? 'disabled' : '' ?>>Borrar (desactivar)</button>
        </form>
      <?php endif; ?>
    </div>
  </section>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>