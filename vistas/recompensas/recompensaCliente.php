<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/RecompensaDAO.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();
$pedido = PedidoService::getPedidoNuevo((int)$user->getId());
$pedidoId = $pedido ? (int)$pedido->getId() : 0;

if (is_post() && isset($_POST['recompensa_id'])) {
    $recompensaId = filter_input(INPUT_POST, 'recompensa_id', FILTER_VALIDATE_INT);
    if (!$pedidoId) {
        flash_set('error', 'Debes iniciar un pedido antes de añadir recompensas.');
    } elseif ($recompensaId) {
        [$ok, $mensaje] = PedidoService::addRecompensaAPedido($pedidoId, $recompensaId, (int)$user->getId());
        flash_set($ok ? 'success' : 'error', $mensaje);
    }
    redirect(RUTA_APP . '/vistas/recompensas/recompensaCliente.php');
}

$recompensas = RecompensaDAO::getAll(false);
$saldo = $user->getBistrocoins();
$gastadosPedido = $pedidoId ? PedidoService::getBistrocoinsGastadosPedido($pedidoId) : 0;
$disponiblesPedido = max(0, $saldo - $gastadosPedido);
$tituloPagina = 'Recompensas | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>
<main>
  <?php foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>
  <div class="panel">
    <div class="actions-inline mb-12">
      <?php if ($pedidoId): ?><a href="<?= RUTA_APP ?>/vistas/pedidos/carrito.php" class="btn primary">🛒 Ver carrito</a><?php endif; ?>
    </div>
    <h2>Recompensas disponibles</h2>
    <p><strong>Tu saldo actual:</strong> <?= (int)$saldo ?> BistroCoins</p>
    <?php if ($pedidoId): ?>
      <p><strong>Reservadas en este pedido:</strong> <?= (int)$gastadosPedido ?> BistroCoins</p>
      <p><strong>Disponibles para seguir canjeando:</strong> <?= (int)$disponiblesPedido ?> BistroCoins</p>
    <?php else: ?>
      <p class="muted">Puedes consultar las recompensas, pero para canjearlas necesitas tener un pedido abierto.</p>
    <?php endif; ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio carta</th>
            <th>Coste</th>
            <th>Estado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recompensas as $recompensa): ?>
          <?php $puede = $disponiblesPedido >= $recompensa->getBistrocoins() && $pedidoId > 0; ?>
          <tr>
            <td>
              <strong><?= e($recompensa->getProductoNombre()) ?></strong><br>
              <span class="muted"><?= e($recompensa->getProductoDescripcion()) ?></span>
            </td>
            <td><?= number_format($recompensa->getProductoPrecioFinal(), 2) ?> €</td>
            <td><?= (int)$recompensa->getBistrocoins() ?> BistroCoins</td>
            <td><?= $puede ? 'Disponible' : 'Saldo insuficiente o sin pedido' ?></td>
            <td>
              <form method="post">
                <input type="hidden" name="recompensa_id" value="<?= (int)$recompensa->getId() ?>">
                <button type="submit" class="btn small" <?= $puede ? '' : 'disabled' ?>>Canjear</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
