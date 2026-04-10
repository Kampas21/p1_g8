<?php


require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioEstadoCamarero.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_role('camarero');

$recibidos     = PedidoService::getPedidosPorEstado('recibido');
$listos_cocina = PedidoService::getPedidosPorEstado('listo_cocina');
$terminados    = PedidoService::getPedidosPorEstado('terminado');

function renderTarjetas(array $pedidos, string $accion): void {
    if (empty($pedidos)): ?>
        <p class="text-muted-italic">No hay pedidos.</p>
    <?php else: foreach ($pedidos as $p): 
        $pedido_id = (int)$p['id'];

        $formObj = new \es\ucm\fdi\aw\Formulario\FormularioEstadoCamarero($pedido_id, $accion);
        $htmlForm = $formObj->gestiona();
    ?>
        <div class="pedido-card">
            <div class="pedido-card-header">
                <span class="pedido-num">#<?= (int)$p['numero_pedido'] ?></span>
                <span><?= $p['tipo'] === 'local' ? '🍽️ Local' : '🥡 Llevar' ?></span>
            </div>

            <div class="pedido-card-body">
                <p><strong>Cliente:</strong> <?= e($p['cliente_nombre']) ?></p>
                <p><strong>Hora:</strong> <?= e(substr($p['fecha_hora'], 11, 5)) ?></p>
                <p><strong>Total:</strong> <?= e($p['total']) ?> €</p>
            </div>

            <?= $htmlForm ?>
        </div>
    <?php endforeach; endif;
}

$tituloPagina = 'Gestión Camarero | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2>Panel de Camarero — <?= e($user->getNombre()) ?></h2>
    
    <div class="tablet-grid">

      <div class="columna">
        <div class="columna-header recibido">
          <span>💰 Pendiente cobro</span>
          <span class="badge"><?= count($recibidos) ?></span>
        </div>
        <div class="columna-body">
          <?php renderTarjetas($recibidos, 'cobrar') ?>
        </div>
      </div>

      <div class="columna">
        <div class="columna-header listo">
          <span>✅ Listos en cocina</span>
          <span class="badge"><?= count($listos_cocina) ?></span>
        </div>
        <div class="columna-body">
          <?php renderTarjetas($listos_cocina, 'entregar') ?>
        </div>
      </div>

      <div class="columna">
        <div class="columna-header terminado">
          <span>🛎️ Para entregar</span>
          <span class="badge"><?= count($terminados) ?></span>
        </div>
        <div class="columna-body">
          <?php renderTarjetas($terminados, 'entregar') ?>
        </div>
      </div>

    </div>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>