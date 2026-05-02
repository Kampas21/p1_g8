<tr>
    <td data-label="Avatar">
        <img class="avatar sm"
             src="<?= escaparHtml($u->getAvatarUrl()) ?>"
             alt="Avatar de <?= escaparHtml($u->getUsername()) ?>">
    </td>

    <td data-label="Usuario">
        <?= escaparHtml($u->getUsername()) ?>
    </td>

    <td data-label="Email">
        <?= escaparHtml($u->getEmail()) ?>
    </td>

    <td data-label="Nombre">
        <?= escaparHtml($u->getNombreCompleto()) ?>
    </td>

    <td data-label="Rol">
        <?= escaparHtml(UsuarioDAO::role_label((string)$u->getRol())) ?>
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
