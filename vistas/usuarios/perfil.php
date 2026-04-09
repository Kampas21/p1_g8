<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioPerfil.php';

use es\ucm\fdi\aw\Formulario\FormularioPerfil;

$user = require_login();

// Si piden quitar el avatar, que es un simple botón POST fuera del form, lo gestionamos aquí
if (is_post() && ($_POST['accion'] ?? '') === 'quitar_avatar_personalizado') {
    require_csrf();
    user_remove_custom_avatar((int) $user->getId());
    flash_set('success', 'Avatar personalizado eliminado.');
    header("Location: perfil.php");
    exit();
}

$form = new FormularioPerfil();
$htmlFormPerfil = $form->gestiona();

$pedidosActivos = [];
$pedidosHistorico = [];
$pedidosDisponibles = false;

$conn = crearConexion();

$checkTable = $conn->query("SHOW TABLES LIKE 'pedidos'");
if ($checkTable && $checkTable->num_rows > 0) {
    $pedidosDisponibles = true;
}

if ($pedidosDisponibles) {
    $uid = (int) $user->getId();

    $sqlActivos = "
        SELECT numero_pedido, estado, fecha_hora, total
        FROM pedidos
        WHERE usuario_id = ?
           AND estado IN ('en_preparacion', 'cocinando', 'listo_cocina', 'terminado')
        ORDER BY fecha_hora DESC
    ";
    $stmtAct = $conn->prepare($sqlActivos);
    if ($stmtAct) {
        $stmtAct->bind_param("i", $uid);
        $stmtAct->execute();
        $resultAct = $stmtAct->get_result();

        while ($row = $resultAct->fetch_assoc()) {
            $pedidosActivos[] = $row;
        }

        $stmtAct->close();
    }

    $sqlHist = "
        SELECT numero_pedido, fecha_hora, tipo, total, estado
        FROM pedidos
        WHERE usuario_id = ?
        ORDER BY fecha_hora DESC
        LIMIT 15
    ";
    $stmtHist = $conn->prepare($sqlHist);
    if ($stmtHist) {
        $stmtHist->bind_param("i", $uid);
        $stmtHist->execute();
        $resultHist = $stmtHist->get_result();

        while ($row = $resultHist->fetch_assoc()) {
            $pedidosHistorico[] = $row;
        }

        $stmtHist->close();
    }
}

$conn->close();

$tituloPagina = 'Perfil | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<div class="panel">
    <h2>Perfil y pedidos</h2>
    <?php
    foreach (flash_get_all() as $item) {
        $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info';
        echo '<div class="notice ' . e($type) . '">' . e($item['message']) . '</div>';
    }
    ?>
</div>

<div class="profile-layout">
    <section class="panel profile-card">
        <h3>Avatar + datos de usuario</h3>

        <div class="profile-top">
            <img class="avatar lg" src="<?= e($user->getAvatarUrl()) ?>" alt="Avatar de <?= e($user->getUsername()) ?>">
            <div>
                <p><strong>Usuario:</strong> <?= e($user->getUsername()) ?></p>
                <p><strong>Email:</strong> <?= e($user->getEmail()) ?></p>
                <p><strong>Nombre y apellidos:</strong> <?= e($user->getNombre()) ?> <?= e($user->getApellidos()) ?></p>
                <p><strong>Rol:</strong> <?= e(role_label((string) $user->getRol()  )) ?></p>
            </div>
        </div>
        <details class="mt-14" open>
            <summary>Foto de perfil / Avatar</summary>

            <img src="<?= e($user->getAvatarUrl()) ?>" alt="Avatar" class="avatar xl">

            <?php if (!empty($user->getAvatarPersonalizado())): ?>
                <form method="post" onsubmit="return confirm('¿Quitar avatar personalizado?');" class="mt-14">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="quitar_avatar">
                    <button class="btn danger" type="submit">Eliminar avatar personalizado</button>
                </form>
            <?php endif; ?>
        </details>
    </section>

    <section class="panel progress-card">
        <h3>Pedidos activos</h3>
        <p class="muted">Estados relevantes: En preparación / Cocinando / Listo cocina / Terminado.</p>

        <?php if (!$pedidosDisponibles): ?>
            <div class="pedido-linea">
                <strong>Ejemplo visual (boceto)</strong>
                <div class="progress-steps">
                    <div class="progress-step done">
                        <div class="dot"></div>En preparación
                    </div>
                    <div class="progress-step done">
                        <div class="dot"></div>Cocinando
                    </div>
                    <div class="progress-step active">
                        <div class="dot"></div>Listo cocina
                    </div>
                    <div class="progress-step">
                        <div class="dot"></div>Terminado
                    </div>
                </div>
            </div>
        <?php elseif (!$pedidosActivos): ?>
            <p>No tienes pedidos activos en este momento.</p>
        <?php else: ?>
            <?php foreach ($pedidosActivos as $p): ?>
                <div class="pedido-linea">
                    <strong>Pedido #<?= e((string) $p['numero_pedido']) ?></strong>
                    <div class="muted">Estado actual: <?= e((string) $p['estado']) ?></div>
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
                    <tr>
                        <th>Nº</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="muted">Sin datos reales todavía.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php elseif (!$pedidosHistorico): ?>
        <p>No hay pedidos registrados todavía.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidosHistorico as $p): ?>
                        <tr>
                            <td><?= e((string) $p['numero_pedido']) ?></td>
                            <td><?= e((string) $p['fecha_hora']) ?></td>
                            <td><?= e((string) $p['tipo']) ?></td>
                            <td><?= e((string) $p['total']) ?></td>
                            <td><?= e((string) $p['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>



<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>