<?php


require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

$user = require_login();

$pedido = null;
if (!empty($_SESSION['ultimo_pedido_id'])) {
  $pedidoId = (int)$_SESSION['ultimo_pedido_id'];
  unset($_SESSION['ultimo_pedido_id']);
  $pedido = PedidoService::getPedidoById($pedidoId);
}

$tituloPagina = 'Pedido confirmado | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<main>
  <div class="panel">
    <h2>Pedido confirmado</h2>
    <p>Tu pedido se ha registrado correctamente.</p>
    <?php if ($pedido): ?>
      <p><strong>Número de pedido:</strong> <?= escaparHtml((string)$pedido->getNumero_pedido()) ?></p>
      <p><strong>Estado:</strong> <?= escaparHtml(ucwords(str_replace('_', ' ', $pedido->getEstado()))) ?></p>
    <?php endif; ?>
    <p>Puedes consultar su estado desde tu perfil o seguir navegando por la aplicación.</p>

    <div class="actions-inline mt-16">
      <a href="<?= RUTA_APP ?>/index.php" class="btn">Volver al inicio</a>
      <a href="<?= RUTA_APP ?>/vistas/usuarios/perfil.php" class="btn primary">Ver mi perfil</a>
    </div>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>