<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioPerfil.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

use es\ucm\fdi\aw\Formulario\FormularioPerfil;

$user = require_login();

if (is_post() && ($_POST['accion'] ?? '') === 'quitar_avatar') {
    UsuarioDAO::user_remove_custom_avatar($user->getId());
    flash_set('success', 'Avatar personalizado eliminado.');
    header("Location: perfil.php");
    exit();
}

$form = new FormularioPerfil();
$htmlFormPerfil = $form->gestiona();

$pedidosActivos = [];
$pedidosHistorico = [];
$pedidosDisponibles = false;
$numPedidosActivos = 0;

$uid = $user->getId();
$tab = $_GET['tab'] ?? 'datos';

try {
    if ($tab === 'activos') {
        $pedidosActivos = PedidoService::getPedidosActivosByUsuario($uid);
    } elseif ($tab === 'historico') {
        $pedidosHistorico = PedidoService::getPedidosHistoricoByUsuario($uid);
    }

    $numPedidosActivos = PedidoService::contarPedidosActivosByUsuario($uid);
    $pedidosDisponibles = true;
} catch (\Exception $e) {
    $pedidosDisponibles = false;
}

$saldoBistrocoins = method_exists($user, 'getBistrocoins') ? (int)$user->getBistrocoins() : 0;

$tituloPagina = 'Perfil | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<div class="panel">
    <h2>Mi Cuenta</h2>

    <div class="actions-inline mb-12">
        <a href="perfil.php?tab=datos" class="btn <?= $tab === 'datos' ? 'editar' : '' ?>">👤 Configuración de Perfil</a>
        <a href="perfil.php?tab=activos" class="btn <?= $tab === 'activos' ? 'editar' : '' ?>">⏳ Pedidos Activos (<?= (int)$numPedidosActivos ?>)</a>
        <a href="perfil.php?tab=historico" class="btn <?= $tab === 'historico' ? 'editar' : '' ?>">📜 Histórico de Pedidos</a>
        <a href="<?= RUTA_APP ?>/vistas/recompensas/recompensaCliente.php" class="btn">🎁 Recompensas</a>
    </div>

    <?php foreach (flash_get_all() as $item): ?>
        <?php $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info'; ?>
        <div class="notice <?= escaparHtml($type) ?>"><?= escaparHtml($item['message']) ?></div>
    <?php endforeach; ?>
</div>

<main class="profile-layout">

    <?php if ($tab === 'datos'): ?>
        <section class="panel profile-card span-full">
            <h3>Mis Datos</h3>

            <div class="profile-top">
                <div class="perfil-avatar-box">
                    <img class="avatar lg"
                         src="<?= escaparHtml($user->getAvatarUrl()) ?>"
                         alt="Avatar de <?= escaparHtml($user->getUsername()) ?>">

                    <?php if ($user->getAvatarTipo() === 'custom'): ?>
                        <form method="post" onsubmit="return confirm('¿Quitar avatar personalizado?');">
                            <input type="hidden" name="accion" value="quitar_avatar">
                            <button class="btn danger small" type="submit">Eliminar foto</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="perfil-datos-box">
                    <p><strong>Usuario:</strong> <?= escaparHtml($user->getUsername()) ?></p>
                    <p><strong>Email:</strong> <?= escaparHtml($user->getEmail()) ?></p>
                    <p><strong>Nombre y apellidos:</strong> <?= escaparHtml($user->getNombre()) ?> <?= escaparHtml($user->getApellidos()) ?></p>
                    <p><strong>Rol:</strong> <?= escaparHtml(UsuarioDAO::role_label((string)$user->getRol())) ?></p>
                    <p><strong>BistroCoins:</strong> <?= (int)$saldoBistrocoins ?></p>
                </div>
            </div>

            <div class="mt-20">
                <?= $htmlFormPerfil ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($tab === 'activos'): ?>
        <section class="span-full">
            <?php include __DIR__ . '/_pedidos_activos.php'; ?>
        </section>
    <?php endif; ?>

    <?php if ($tab === 'historico'): ?>
        <section class="span-full">
            <?php include __DIR__ . '/_pedidos_historico.php'; ?>
        </section>
    <?php endif; ?>

</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>