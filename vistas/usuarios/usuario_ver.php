<?php
declare(strict_types=1);



require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';

$admin = require_role('gerente');
$id = (int)($_GET['id'] ?? 0);
$user = $id > 0 ? user_find_by_id($id) : null;

if (!$user) {
    flash_set('error', 'Usuario no encontrado.');
    redirect(RUTA_APP . 'usuarios.php');
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
      <img class="avatar lg" src="<?= e($user->getAvatarUrl()) ?>" alt="Avatar de <?= e($user->getUsername()) ?>">
      <div>
        <dl>
          <p><strong>ID:</strong> <?= $user->getId() ?></p>
          <p><strong>Usuario:</strong> <?= e($user->getUsername()) ?></p>
          <p><strong>Email:</strong> <?= e($user->getEmail()) ?></p>
          <p><strong>Nombre:</strong> <?= e($user->getNombre()) ?></p>
          <p><strong>Apellidos:</strong> <?= e($user->getApellidos()) ?></p>
          <p><strong>Rol:</strong> <?= e(role_label((string)$user->getRol())) ?></p>
          <p><strong>Estado:</strong> <?= $user->isActivo() ? '<span class="texto-exito">Activo</span>' : '<span class="texto-error">Inactivo</span>' ?></p>
          <p><strong>Actualizado:</strong> <?= e($user->getUpdatedAt()) ?></p>
        </dl>
      </div>
    </div>

    <div class="actions-inline mt-14">
      <a class="btn" href="usuarios.php">Volver al listado</a>
      <a class="btn primary" href="usuario_form.php?id=<?= $user->getId() ?>">Editar usuario</a>

      <?php if ($user->isActivo()): ?>
        <form method="post" action="usuario_eliminar.php" onsubmit="return confirm('¿Desactivar este usuario?');" class="d-inline">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="id" value="<?= $user->getId() ?>">
          <button class="btn danger" type="submit" <?= $user->getId() === $admin->getId() ? 'disabled' : '' ?>>Borrar (desactivar)</button>
        </form>
      <?php endif; ?>
    </div>
  </section>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>