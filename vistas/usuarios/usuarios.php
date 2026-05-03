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
            <div class="notice <?= escaparHtml($type) ?>">
                <?= escaparHtml($item['message']) ?>
            </div>
        <?php endforeach; ?>

        <form method="get" class="actions-inline">
            <label for="q" class="m-0">Buscar</label>

            <input id="q"
                   type="text"
                   name="q"
                   value="<?= escaparHtml($search) ?>"
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
                            <?php require __DIR__ . '/_fila_usuario.php'; ?>
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