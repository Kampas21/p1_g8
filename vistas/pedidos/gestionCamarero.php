<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';

$user = require_role('camarero');

if (is_post()) {
    $pedido_id = (int)($_POST['pedido_id'] ?? 0);
    $accion    = $_POST['accion'] ?? '';

    $transiciones = [
        'cobrar'    => ['de' => 'recibido',     'a' => 'en_preparacion'],
        'preparado' => ['de' => 'listo_cocina', 'a' => 'terminado'],
        'entregar'  => ['de' => 'terminado',    'a' => 'entregado'],
    ];

    if ($pedido_id > 0 && isset($transiciones[$accion])) {
        $pedido = Pedido::getPedidoById($pedido_id);
        $t = $transiciones[$accion];
        if ($pedido && $pedido['estado'] === $t['de']) {
            Pedido::cambiarEstado($pedido_id, $t['a']);
        }
    }

    redirect('gestionCamarero.php');
}

$recibidos     = Pedido::getPedidosPorEstado('recibido');
$listos_cocina = Pedido::getPedidosPorEstado('listo_cocina');
$terminados    = Pedido::getPedidosPorEstado('terminado');

function renderTarjetas(array $pedidos, string $accion, string $btnLabel): void {
    if (empty($pedidos)): ?>
        <p style="color: #666; font-style: italic;">No hay pedidos.</p>
    <?php else: foreach ($pedidos as $p): ?>
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
            <form method="POST" action="gestionCamarero.php">
                <input type="hidden" name="pedido_id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="accion"    value="<?= e($accion) ?>">
                <button type="submit" class="btn primary" style="width: 100%; margin-top: 10px;"><?= e($btnLabel) ?></button>
            </form>
        </div>
    <?php endforeach; endif;
}

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Gestión Camarero | Bistro FDI';
ob_start();
?>
<style>
    /* Estilos adaptados para encajar dentro del panel central */
    .tablet-grid { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px; }
    .columna { flex: 1; min-width: 250px; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
    .columna-header { padding: 12px; font-weight: bold; font-size: 1.1em; color: white; display: flex; justify-content: space-between; align-items: center; }
    .columna-header.recibido { background-color: #f44336; }
    .columna-header.listo { background-color: #2196F3; }
    .columna-header.terminado { background-color: #4CAF50; }
    .columna-body { padding: 15px; display: flex; flex-direction: column; gap: 15px; background: #f9f9f9; min-height: 200px; }
    .pedido-card { background: white; padding: 15px; border-radius: 6px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .pedido-card-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
    .pedido-card-body p { margin: 5px 0; }
    .pedido-num { font-size: 1.2em; font-weight: bold; color: #333; }
    .badge { background: rgba(0,0,0,0.2); padding: 3px 10px; border-radius: 20px; font-size: 0.9em; }
</style>

<div class="panel">
    <h2>Panel de Camarero — <?= e($user['nombre']) ?></h2>
    
    <div class="tablet-grid">
      <div class="columna">
        <div class="columna-header recibido">
          <span>💰 Pendiente cobro</span>
          <span class="badge"><?= count($recibidos) ?></span>
        </div>
        <div class="columna-body">
          <?php renderTarjetas($recibidos, 'cobrar', 'Cobrado → En preparación') ?>
        </div>
      </div>

      <div class="columna">
        <div class="columna-header listo">
          <span>✅ Listos en cocina</span>
          <span class="badge"><?= count($listos_cocina) ?></span>
        </div>
        <div class="columna-body">
          <?php renderTarjetas($listos_cocina, 'preparado', 'Revisado → Listo para entregar') ?>
        </div>
      </div>

      <div class="columna">
        <div class="columna-header terminado">
          <span>🛎️ Para entregar</span>
          <span class="badge"><?= count($terminados) ?></span>
        </div>
        <div class="columna-body">
          <?php renderTarjetas($terminados, 'entregar', 'Entregado al cliente') ?>
        </div>
      </div>
    </div>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>