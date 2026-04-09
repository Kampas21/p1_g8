<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';

$user    = require_login();
$pedidos = Pedido::getPedidosDeUsuario((int)$user->getId());

$etiquetas = [
    'recibido'       => 'Pendiente de pago',
    'en_preparacion' => 'En preparación',
    'cocinando'      => 'Cocinando',
    'listo_cocina'   => 'Listo en cocina',
    'terminado'      => 'Listo para recoger',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Mis pedidos | Bistro FDI';
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
    <div class="actions-inline" style="justify-content:space-between; align-items:center;">
      <h2 style="margin:0;">Mis pedidos</h2>
      <a href="elegirTipo.php" class="btn primary">+ Nuevo pedido</a>
    </div>
  </div>

  <?php if (empty($pedidos)): ?>
    <div class="panel">
      <p>Todavía no tienes pedidos.</p>
      <a href="elegirTipo.php" class="btn primary">Hacer mi primer pedido</a>
    </div>

  <?php else: ?>
    <div class="panel">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nº pedido</th>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($pedidos as $p): ?>
            <tr>
              <td><strong>#<?= e($p['numero_pedido']) ?></strong></td>
              <td><?= e(substr($p['fecha_hora'], 0, 16)) ?></td>
              <td><?= $p['tipo'] === 'local' ? '🍽️ Local' : '🥡 Llevar' ?></td>
              <td><?= e($etiquetas[$p['estado']] ?? $p['estado']) ?></td>
              <td><?= e($p['total']) ?> €</td>
              <td>
                <a href="estadoPedido.php?id=<?= (int)$p['id'] ?>" class="btn small">
                  Ver detalle
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>