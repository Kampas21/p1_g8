<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../includes/user_repo.php';
require_once __DIR__ . '/../includes/auth.php';

$admin = require_role('gerente');

if (is_post()) {
    require_csrf();
    $accion = (string)($_POST['accion'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0 && $accion === 'reactivar') {
        user_reactivate($id);
        flash_set('success', 'Usuario reactivado.');
        redirect('../entities/usuarios.php?ver=todo');
    }
}

$search = trim((string)($_GET['q'] ?? ''));
$includeInactive = (string)($_GET['ver'] ?? '') === 'todo';
$users = user_list(['search' => $search, 'include_inactive' => $includeInactive]);

$tituloPagina = 'Listado de usuarios | Bistro FDI';
$rutaCSS = '/p1_g8/CSS/estilo.css';

ob_start();
?>
<div class="panel">
    <h2>Gestión de usuarios (Gerente)</h2>
    
    <?php
    // Reemplazo de layout_flash_messages()
    foreach (flash_get_all() as $item) {
        $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info';
        echo '<div class="notice ' . e($type) . '">' . e($item['message']) . '</div>';
    }
    ?>

    <form method="get" class="actions-inline">
        <label for="q" style="margin:0;">Buscar</label>
        <input id="q" type="text" name="q" value="<?= e($search) ?>" style="width:260px;">
        <label><input type="checkbox" name="ver" value="todo" <?= $includeInactive ? 'checked' : '' ?>> Mostrar inactivos</label>
        <button type="submit">Aplicar</button>
        <a class="btn" href="../entities/usuarios.php">Limpiar</a>
        <a class="btn primary" href="../vistas/usuarios/usuario_form.php?modo=crear">Crear nuevo usuario</a>
    </form>
</div>

<section class="panel">
    <h3>Listado / visualización</h3>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="7">No hay usuarios que coincidan con el filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><img class="avatar sm" src="<?= e($u['avatar_url']) ?>" alt="Avatar de <?= e($u['username']) ?>"></td>
                            <td><?= e($u['username']) ?></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['nombre'] . ' ' . $u['apellidos']) ?></td>
                            <td><?= e(role_label((string)$u['rol'])) ?></td>
                            <td><?= (int)$u['activo'] === 1 ? 'Activo' : 'Inactivo' ?></td>
                            <td>
                                <div class="actions-inline">
                                    <a class="btn small" href="/p1_g8/vistas/usuarios/usuario_ver.php?id=<?= (int)$u['id'] ?>">Ver</a>
                                    <a class="btn small" href="/p1_g8/vistas/usuarios/usuario_form.php?id=<?= (int)$u['id'] ?>">Editar</a>

                                    <?php if ((int)$u['activo'] === 1): ?>
                                        <form method="post" action="/p1_g8/vistas/usuarios/usuario_eliminar.php" onsubmit="return confirm('¿Desactivar este usuario?');" style="display:inline;">
                                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                            <button class="btn small danger" type="submit" <?= (int)$u['id'] === (int)$admin['id'] ? 'disabled title="No puedes desactivarte a ti mismo/a"' : '' ?>>Borrar</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="accion" value="reactivar">
                                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                            <button class="btn small" type="submit">Reactivar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../includes/plantilla.php';
?>