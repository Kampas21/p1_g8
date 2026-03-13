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
        <p class="muted">No hay pedidos.</p>
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
                <button type="submit" class="btn primary"><?= e($btnLabel) ?></button>
            </form>
        </div>
    <?php endforeach; endif;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión Camarero | Bistro FDI</title>
  <!-- Asegúrate de cargar los estilos necesarios -->
  <link rel="stylesheet" href="<?= RUTA_APP ?>/CSS/estilo.css">
  <style>
      /* Estilos específicos para la vista apaisada de la tablet si no los tienes en estilo.css */
      .tablet-grid { display: flex; gap: 20px; padding: 20px; align-items: flex-start; }
      .columna { flex: 1; background: #f9f9f9; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
      .columna-header { padding: 15px; font-weight: bold; font-size: 1.1em; color: white; display: flex; justify-content: space-between; }
      .columna-header.recibido { background-color: #f44336; }
      .columna-header.listo { background-color: #2196F3; }
      .columna-header.terminado { background-color: #4CAF50; }
      .columna-body { padding: 15px; display: flex; flex-direction: column; gap: 15px; }
      .pedido-card { background: white; padding: 15px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
      .pedido-card-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
      .pedido-num { font-size: 1.2em; font-weight: bold; }
      .btn.primary { width: 100%; margin-top: 10px; }
      .badge { background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; }
  </style>
</head>
<body>

<header class="site-header" style="background:#333; color:white; padding:15px 20px; display:flex; justify-content:space-between; align-items:center;">
    <div style="display:flex; align-items:center; gap:15px;">
      <h1>Camarero — <?= e($user['nombre']) ?></h1>
    </div>
    <nav>
      <a href="<?= RUTA_APP ?>/vistas/usuarios/logout.php" style="color:white; text-decoration:none; border:1px solid white; padding:5px 10px; border-radius:4px;">Salir</a>
    </nav>
</header>

<div class="tablet-grid">

  <div class="columna">
    <div class="columna-header recibido">
      💰 Pendiente de cobro
      <span class="badge"><?= count($recibidos) ?></span>
    </div>
    <div class="columna-body">
      <?php renderTarjetas($recibidos, 'cobrar', 'Cobrado → En preparación') ?>
    </div>
  </div>

  <div class="columna">
    <div class="columna-header listo">
      ✅ Listos en cocina
      <span class="badge"><?= count($listos_cocina) ?></span>
    </div>
    <div class="columna-body">
      <?php renderTarjetas($listos_cocina, 'preparado', 'Revisado → Listo para entregar') ?>
    </div>
  </div>

  <div class="columna">
    <div class="columna-header terminado">
      🛎️ Para entregar
      <span class="badge"><?= count($terminados) ?></span>
    </div>
    <div class="columna-body">
      <?php renderTarjetas($terminados, 'entregar', 'Entregado al cliente') ?>
    </div>
  </div>

</div>

</body>
</html>