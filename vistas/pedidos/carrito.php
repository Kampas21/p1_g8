<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php'; 
require_once __DIR__ . '/../../includes/auth.php'; 
require_once __DIR__ . '/../../includes/util.php'; 
require_once __DIR__ . '/../../entities/pedido.php';

$user = require_login();
$usuario_id = (int)$user['id'];

$pedido = Pedido::getPedidoNuevo($usuario_id);
if (!$pedido) {
    redirect('elegirTipo.php'); 
}
$pedido_id = $pedido['id'];

// Procesar acciones POST
if (is_post()) {
    $accion      = $_POST['accion'] ?? '';
    $producto_id = (int)($_POST['producto_id'] ?? 0);

    if ($accion === 'actualizar' && $producto_id > 0) {
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        if ($cantidad <= 0) {
            Pedido::removeProducto($pedido_id, $producto_id);
            flash_set('success', 'Producto eliminado del carrito.');
        } else {
            Pedido::updateCantidad($pedido_id, $producto_id, $cantidad);
            flash_set('success', 'Cantidad actualizada.');
        }
        redirect('carrito.php');
    }

    if ($accion === 'eliminar' && $producto_id > 0) {
        Pedido::removeProducto($pedido_id, $producto_id);
        flash_set('success', 'Producto eliminado.');
        redirect('carrito.php');
    }

    if ($accion === 'cancelar') {
        Pedido::cancelarPedido($pedido_id);
        flash_set('success', 'Pedido cancelado.');
        redirect('elegirTipo.php');
    }

    if ($accion === 'confirmar') {
        redirect('pago.php');
    }
}

$lineas = Pedido::getProductosPedido($pedido_id);

$total = 0;
foreach ($lineas as $linea) {
    $total += $linea['precio_unitario'] * $linea['cantidad'];
}
$total = round($total, 2);

// ---- EMPIEZA LA VISTA ----
$tituloPagina = 'Mi carrito | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  
  <?php 
  foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <h2>Mi carrito
      <span style="font-size:14px; font-weight:normal; color:#888;">
        — pedido <?= e($pedido['tipo'] === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
      </span>
    </h2>

    <?php if (empty($lineas)): ?>
      <p>El carrito está vacío.</p>
      <a href="catalogo.php" class="btn">← Volver al catálogo</a>

    <?php else: ?>
      <div class="table-wrap">
                <table>
          <thead>
            <tr>
              <th>Imagen</th> <!-- NUEVA CABECERA -->
              <th>Producto</th>
              <th>Precio ud.</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($lineas as $linea): ?>
            <tr>
              <!-- NUEVA COLUMNA DE IMAGEN -->
              <td style="text-align: center;">
                <?php if (!empty($linea['imagen'])): ?>
                  <img src="<?= RUTA_APP . '/' . e($linea['imagen']) ?>" alt="<?= e($linea['nombre']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                  <span style="font-size: 12px; color: #888;">(Sin imagen)</span>
                <?php endif; ?>
              </td>
              
              <td><?= e($linea['nombre']) ?></td>
              <td><?= e($linea['precio_unitario']) ?> €</td>
              <td>
                <form method="POST" action="carrito.php" style="display:inline-flex;gap:6px;align-items:center;">
                  <input type="hidden" name="accion" value="actualizar">
                  <input type="hidden" name="producto_id" value="<?= (int)$linea['producto_id'] ?>">
                  <input type="number" name="cantidad" value="<?= (int)$linea['cantidad'] ?>" min="0" style="width:54px;padding:4px;">
                  <button type="submit" class="btn small">OK</button>
                </form>
              </td>
              <td><?= round($linea['precio_unitario'] * $linea['cantidad'], 2) ?> €</td>
              <td>
                <form method="POST" action="carrito.php">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="producto_id" value="<?= (int)$linea['producto_id'] ?>">
                  <button type="submit" class="btn danger small">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <!-- Aumentamos el colspan a 4 porque hemos añadido una columna de imagen -->
              <td colspan="4" style="text-align: right; padding-right: 15px;"><strong>Total:</strong></td>
              <td colspan="2"><strong><?= $total ?> €</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="actions-inline" style="margin-top:16px;">
        <a href="catalogo.php" class="btn">← Seguir añadiendo</a>

        <form method="POST" action="carrito.php" style="display:inline-block;">
          <input type="hidden" name="accion" value="confirmar">
          <button type="submit" class="btn primary">Confirmar pedido →</button>
        </form>

        <form method="POST" action="carrito.php" style="display:inline-block;">
          <input type="hidden" name="accion" value="cancelar">
          <button type="submit" class="btn danger"
            onclick="return confirm('¿Seguro que quieres cancelar el pedido?')">
            Cancelar pedido
          </button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>