<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();

$pedido_id = (int)($_SESSION['ultimo_pedido_id'] ?? 0);
unset($_SESSION['ultimo_pedido_id']);

if (!$pedido_id) {
    redirect('elegirTipo.php');
}

$pedido = PedidoService::getPedidoById($pedido_id);
if (!$pedido) {
    redirect('elegirTipo.php');
}

$etiquetas_estado = [
    'recibido'       => 'Recibido — pendiente de pago al camarero',
    'en_preparacion' => 'En preparación',
];

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Pedido confirmado | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  
  <?php 
  // Mostrar mensajes flash si hubiera alguno
  foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <h2>¡Pedido confirmado! ✅</h2>
    <p>Tu pedido ha sido registrado correctamente.</p>

    <table>
      <tbody>
        <tr>
          <th>Número de pedido</th>
          <td><strong>#<?= e($pedido['numero_pedido']) ?></strong></td>
        </tr>
        <tr>
          <th>Estado</th>
          <td><?= e($etiquetas_estado[$pedido['estado']] ?? $pedido['estado']) ?></td>
        </tr>
        <tr>
          <th>Tipo</th>
          <td><?= $pedido['tipo'] === 'local' ? '🍽️ En local' : '🥡 Para llevar' ?></td>
        </tr>
        <tr>
          <th>Método de pago</th>
          <td><?= $pedido['metodo_pago'] === 'tarjeta' ? '💳 Tarjeta' : '💵 Al camarero' ?></td>
        </tr>
        <tr>
          <th>Total</th>
          <td><?= e($pedido['total']) ?> €</td>
        </tr>
        <tr>
          <th>Fecha y hora</th>
          <td><?= e($pedido['fecha_hora']) ?></td>
        </tr>
      </tbody>
    </table>

    <?php if ($pedido['metodo_pago'] === 'camarero'): ?>
      <div class="mensaje-info" class="mensaje-info panel-info-border mt-16">
        Dirígete al mostrador para pagar. Tu número de pedido es <strong>#<?= e($pedido['numero_pedido']) ?></strong>.
      </div>
    <?php endif; ?>

    <div class="actions-inline mt-16">
      <a href="listarPedidosCliente.php" class="btn primary">Ver mis pedidos</a>
      <a href="elegirTipo.php" class="btn">Hacer otro pedido</a>
    </div>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>
