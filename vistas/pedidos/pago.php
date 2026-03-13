<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';

$user      = require_login();
$usuario_id = (int)$user['id'];

$pedido = Pedido::getPedidoNuevo($usuario_id);
if (!$pedido) {
    redirect('elegirTipo.php');
}
$pedido_id = $pedido['id'];

$lineas = Pedido::getProductosPedido($pedido_id);
if (empty($lineas)) {
    redirect('carrito.php');
}

$total = 0;
foreach ($lineas as $linea) {
    $total += $linea['precio_unitario'] * $linea['cantidad'];
}
$total = round($total, 2);

$errores = [];

if (is_post()) {
    $metodo = $_POST['metodo_pago'] ?? '';

    if ($metodo === 'camarero') {
        Pedido::confirmarPedido($pedido_id, 'camarero', $total);
        $_SESSION['ultimo_pedido_id'] = $pedido_id;
        redirect('confirmacion.php');
    }

    if ($metodo === 'tarjeta') {
        $numero    = trim($_POST['numero_tarjeta'] ?? '');
        $nombre    = trim($_POST['nombre_tarjeta'] ?? '');
        $caducidad = trim($_POST['caducidad']      ?? '');
        $cvv       = trim($_POST['cvv']            ?? '');

        if (!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $numero))) {
            $errores['numero_tarjeta'] = 'El número de tarjeta debe tener 16 dígitos.';
        }
        if ($nombre === '') {
            $errores['nombre_tarjeta'] = 'Introduce el nombre del titular.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $caducidad)) {
            $errores['caducidad'] = 'Formato de caducidad inválido (MM/AA).';
        }
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            $errores['cvv'] = 'El CVV debe tener 3 o 4 dígitos.';
        }

        if (empty($errores)) {
            Pedido::confirmarPedido($pedido_id, 'tarjeta', $total);
            $_SESSION['ultimo_pedido_id'] = $pedido_id;
            redirect('confirmacion.php');
        }
    }

    if ($metodo === '') {
        $errores['metodo_pago'] = 'Selecciona un método de pago.';
    }
}

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Pago del pedido | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <div class="panel">
    <div class="actions-inline" style="margin-bottom:12px;">
      <a href="carrito.php" class="btn">← Volver al carrito</a>
    </div>

    <h2>Pago</h2>
    <p>Total a pagar: <strong><?= $total ?> €</strong></p>

    <?php if (isset($errores['metodo_pago'])): ?>
      <div class="mensaje-error"><?= e($errores['metodo_pago']) ?></div>
    <?php endif; ?>
  </div>

  <form method="POST" action="pago.php">

    <!-- Pagar al camarero -->
    <div class="panel">
      <h3>💵 Pagar al camarero</h3>
      <p>El camarero pasará a cobrarle en su mesa o en el mostrador.</p>
      <button type="submit" name="metodo_pago" value="camarero" class="btn primary">
        Pagar al camarero
      </button>
    </div>

    <!-- Pagar con tarjeta -->
    <div class="panel">
      <h3>💳 Pagar con tarjeta</h3>

      <div class="form-grid">
        <div class="full">
          <label>Número de tarjeta</label>
          <input type="text" name="numero_tarjeta" maxlength="19"
                 placeholder="1234 5678 9012 3456"
                 value="<?= e($_POST['numero_tarjeta'] ?? '') ?>">
          <?php if (isset($errores['numero_tarjeta'])): ?>
            <div class="mensaje-error"><?= e($errores['numero_tarjeta']) ?></div>
          <?php endif; ?>
        </div>

        <div class="full">
          <label>Nombre del titular</label>
          <input type="text" name="nombre_tarjeta" placeholder="NOMBRE APELLIDO"
                 value="<?= e($_POST['nombre_tarjeta'] ?? '') ?>">
          <?php if (isset($errores['nombre_tarjeta'])): ?>
            <div class="mensaje-error"><?= e($errores['nombre_tarjeta']) ?></div>
          <?php endif; ?>
        </div>

        <div>
          <label>Caducidad</label>
          <input type="text" name="caducidad" maxlength="5" placeholder="MM/AA"
                 value="<?= e($_POST['caducidad'] ?? '') ?>">
          <?php if (isset($errores['caducidad'])): ?>
            <div class="mensaje-error"><?= e($errores['caducidad']) ?></div>
          <?php endif; ?>
        </div>

        <div>
          <label>CVV</label>
          <input type="text" name="cvv" maxlength="4" placeholder="123"
                 value="<?= e($_POST['cvv'] ?? '') ?>">
          <?php if (isset($errores['cvv'])): ?>
            <div class="mensaje-error"><?= e($errores['cvv']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div style="margin-top:14px;">
        <button type="submit" name="metodo_pago" value="tarjeta" class="btn primary">
          Pagar <?= $total ?> € con tarjeta
        </button>
      </div>
    </div>

  </form>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>