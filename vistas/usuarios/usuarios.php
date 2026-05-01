<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

$admin = require_role('gerente');

$search = trim((string)($_GET['q'] ?? ''));
$includeInactive = (string)($_GET['ver'] ?? '') === 'todo';

$users = UsuarioDAO::user_list([
    'search' => $search,
    'include_inactive' => $includeInactive
]);

$tituloPagina = 'Listado de usuarios | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<main>
    <header class="panel">
        <h2>Gestión de usuarios (Gerente)</h2>

        <?php foreach (flash_get_all() as $item): ?>
            <?php $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info'; ?>
            <div class="notice <?= e($type) ?>">
                <?= e($item['message']) ?>
            </div>
        <?php endforeach; ?>

        <form method="get" class="actions-inline">
            <label for="q" class="m-0">Buscar</label>

            <input id="q"
                   type="text"
                   name="q"
                   value="<?= e($search) ?>"
                   class="w-260">

            <label>
                <input type="checkbox"
                       name="ver"
                       value="todo"
                       <?= $includeInactive ? 'checked' : '' ?>>
                Mostrar inactivos
            </label>

            <button type="submit">Aplicar</button>

            <a class="btn" href="usuarios.php">Limpiar</a>
            <a class="btn primary" href="usuario_form.php?modo=crear">Crear nuevo usuario</a>
        </form>
    </header>

    <section class="panel">
        <h3>Listado general</h3>

        <div class="table-wrap">
            <table class="w-full tabla-movil">
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
                        <tr>
                            <td colspan="7" data-label="">
                                No hay usuarios que coincidan con el filtro.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td data-label="Avatar">
                                    <img class="avatar sm"
                                         src="<?= e($u->getAvatarUrl()) ?>"
                                         alt="Avatar de <?= e($u->getUsername()) ?>">
                                </td>

                                <td data-label="Usuario">
                                    <?= e($u->getUsername()) ?>
                                </td>

                                <td data-label="Email">
                                    <?= e($u->getEmail()) ?>
                                </td>

                                <td data-label="Nombre">
                                    <?= e($u->getNombreCompleto()) ?>
                                </td>

                                <td data-label="Rol">
                                    <?= e(UsuarioDAO::role_label((string)$u->getRol())) ?>
                                </td>

                                <td data-label="Estado">
                                    <?= $u->isActivo()
                                        ? '<span class="texto-exito">Activo</span>'
                                        : '<span class="texto-error">Inactivo</span>' ?>
                                </td>

                                <td data-label="Acciones">
                                    <div class="actions-inline icon-actions">
                                        <a class="btn small icon-btn"
                                           href="usuario_ver.php?id=<?= (int)$u->getId() ?>"
                                           title="Ver detalles">
                                            👁️
                                        </a>

                                        <a class="btn small primary icon-btn"
                                           href="usuario_form.php?id=<?= (int)$u->getId() ?>"
                                           title="Editar usuario">
                                            ✏️
                                        </a>

                                        <?php if ($u->isActivo()): ?>
                                            <form method="post"
                                                  action="../../scripts/usuarios/usuario_eliminar.php"
                                                  onsubmit="return confirm('¿Desactivar este usuario?');"
                                                  class="d-inline m-0">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int)$u->getId() ?>">

                                                <button class="btn small danger icon-btn"
                                                        type="submit"
                                                        title="Desactivar usuario"
                                                        <?= $u->getId() === $admin->getId() ? 'disabled' : '' ?>>
                                                    🗑️
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post"
                                                  action="../../scripts/usuarios/usuario_reactivar.php"
                                                  class="d-inline m-0">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int)$u->getId() ?>">

                                                <button class="btn small success icon-btn"
                                                        type="submit"
                                                        title="Reactivar usuario">
                                                    ♻️
                                                </button>
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
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>