<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';

$user = require_login();
$usuario_id = (int)$user['id'];

// Si ya tiene un carrito/pedido activo, no le dejamos elegir de nuevo, le mandamos al carrito
$pedidoActivo = Pedido::getPedidoNuevo($usuario_id);
if ($pedidoActivo) {
    redirect('carrito.php');
}

if (is_post()) {
    $tipo = $_POST['tipo'] ?? null;

    if ($tipo === 'local' || $tipo === 'llevar') {
        Pedido::crearPedido($usuario_id, $tipo);
        redirect('catalogo.php');
    }

    $error = 'Debes elegir un tipo de pedido.';
}

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Elegir tipo de pedido | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <?php 
  // Mostrar mensajes flash si los hubiera
  foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <h2>¿Cómo quieres tu pedido?</h2>

    <?php if (isset($error)): ?>
      <div class="mensaje-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="elegirTipo.php">
      <div class="actions-inline">
        <button type="submit" name="tipo" value="local" class="btn primary" style="font-size:16px; padding:14px 24px;">
          🍽️ Consumir en el local
        </button>
        <button type="submit" name="tipo" value="llevar" class="btn primary" style="font-size:16px; padding:14px 24px;">
          🥡 Para llevar
        </button>
      </div>
    </form>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>