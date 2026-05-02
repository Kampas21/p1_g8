<?php
session_start();

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/RecompensaDAO.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

$user = require_login();

if (is_post() && isset($_POST['recompensa_id'])) {
    $recompensaId = filter_input(INPUT_POST, 'recompensa_id', FILTER_VALIDATE_INT);

    if ($recompensaId) {
        [$ok, $mensaje] = PedidoService::addRecompensaAlCarrito($recompensaId, (int)$user->getId());
        flash_set($ok ? 'success' : 'error', $mensaje);
    }

    redirect(RUTA_APP . '/vistas/recompensas/recompensaCliente.php');
}

$recompensas = RecompensaDAO::getAll(false);
$saldo = $user->getBistrocoins();

$gastadosPedido = 0;

foreach (PedidoService::getCarritoItems() as $item) {
    if (!empty($item['es_recompensa'])) {
        $gastadosPedido += ((int)$item['bistrocoins_unitarios']) * ((int)$item['cantidad']);
    }
}

$disponiblesPedido = max(0, $saldo - $gastadosPedido);
$pedidoId = PedidoService::carritoTieneTipo() ? 1 : 0;

$tituloPagina = 'Recompensas | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<main>
    <?php foreach (flash_get_all() as $f): ?>
        <div class="mensaje-<?= escaparHtml($f['type']) ?>">
            <?= escaparHtml($f['message']) ?>
        </div>
    <?php endforeach; ?>

    <div class="panel">
        <div class="actions-inline mb-12">
            <?php if ($pedidoId): ?>
                <a href="<?= RUTA_APP ?>/vistas/pedidos/carrito.php" class="btn primary">
                    🛒 Ver carrito
                </a>
            <?php endif; ?>
        </div>

        <h2>Recompensas disponibles</h2>

        <p><strong>Tu saldo actual:</strong> <?= (int)$saldo ?> BistroCoins</p>

        <?php if ($pedidoId): ?>
            <p><strong>Reservadas en este pedido:</strong> <?= (int)$gastadosPedido ?> BistroCoins</p>
            <p><strong>Disponibles para seguir canjeando:</strong> <?= (int)$disponiblesPedido ?> BistroCoins</p>
        <?php else: ?>
            <p class="muted">
                Puedes consultar las recompensas, pero para canjearlas necesitas tener un pedido abierto.
            </p>
        <?php endif; ?>

        <div class="table-wrap">
            <table class="tabla-movil">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio carta</th>
                        <th>Coste</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($recompensas as $recompensa): ?>
                        <?php require __DIR__ . '/_fila_recompensa.php'; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>