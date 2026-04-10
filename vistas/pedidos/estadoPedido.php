<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$pedido_id) {
    redirect('listarPedidosCliente.php');
}

$pedido = PedidoService::getPedidoById($pedido_id);

if (!$pedido || (int)$pedido->getUsuario_id() !== $usuario_id) {
    http_response_code(403);
    
    $tituloPagina = 'Acceso denegado | Bistro FDI';
    $rutaCSS = RUTA_APP . '/CSS/estilo.css';
    ob_start();
    echo '<main><div class="panel"><p>Pedido no encontrado o no tienes permiso para verlo.</p><a href="listarPedidosCliente.php" class="btn">← Volver</a></div></main>';
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit();
}

$lineas = PedidoService::getProductosPedido($pedido_id);

if (is_post() && ($_POST['accion'] ?? '') === 'cancelar') {
    if ($pedido->getEstado() === 'recibido') {
        PedidoService::cancelarPedido($pedido_id);
    }
    flash_set('success', 'Pedido cancelado correctamente.');
    redirect('listarPedidosCliente.php');
}

$etiquetas = [
    'recibido'       => 'Recibido — pendiente de pago',
    'en_preparacion' => 'En preparación',
    'cocinando'      => 'Cocinando',
    'listo_cocina'   => 'Listo en cocina',
    'terminado'      => 'Listo para recoger',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Pedido #' . $pedido->getNumero_pedido() . ' | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <?php 
  // Mostrar mensajes flash
  foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <div class="actions-inline mb-12">
      <a href="listarPedidosCliente.php" class="btn">← Mis pedidos</a>
    </div>

    <h2>Pedido #<?= e($pedido->getNumero_pedido()) ?></h2>

    <table>
      <tbody>
        <tr>
          <th>Estado</th>
          <td><strong><?= e($etiquetas[$pedido->getEstado()] ?? $pedido->getEstado()) ?></strong></td>
        </tr>
        <tr>
          <th>Tipo</th>
          <td><?= $pedido->getTipo() === 'local' ? '🍽️ En local' : '🥡 Para llevar' ?></td>
        </tr>
        <tr>
          <th>Pago</th>
          <td><?= $pedido->getMetodo_pago() === 'tarjeta' ? '💳 Tarjeta' : ($pedido->getMetodo_pago() === 'camarero' ? '💵 Al camarero' : '—') ?></td>
        </tr>
        <tr>
          <th>Fecha</th>
          <td><?= e($pedido->getFecha_hora()) ?></td>
        </tr>
        <tr>
          <th>Total</th>
          <td><?= e($pedido->getTotal()) ?> €</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="panel">
    <h3>Productos</h3>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio ud.</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($lineas as $linea): ?>
          <tr>
            <td><?= e($linea->getNombre()) ?></td>
            <td><?= e($linea->getPrecio()) ?> €</td>
            <td><?= (int)$linea->getCantidad() ?></td>
            <td><?= round($linea->getPrecio() * $linea->getCantidad(), 2) ?> €</td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td><strong><?= e($pedido->getTotal()) ?> €</strong></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <?php if ($pedido->getEstado() === 'recibido'): ?>
  <div class="panel" class="panel mt-20 panel-danger-border">
    <div class="mensaje-info" class="mensaje-info mb-12">
      Este pedido aún no ha sido pagado. Puedes cancelarlo si ya no lo necesitas.
    </div>
    <form method="POST" action="estadoPedido.php?id=<?= (int)$pedido_id ?>" class="mt-10">
      <input type="hidden" name="accion" value="cancelar">
      <button type="submit" class="btn danger"
        onclick="return confirm('¿Seguro que quieres cancelar este pedido?')">
        Cancelar pedido
      </button>
    </form>
  </div>
  <?php endif; ?>

</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>
