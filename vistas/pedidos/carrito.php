<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';

// Requerimos los nuevos formularios
require_once __DIR__ . '/../../includes/Formulario/FormularioActualizarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioEliminarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCancelarPedido.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = Pedido::getPedidoNuevo($usuario_id);
if (!$pedido) {
  redirect('elegirTipo.php');
}
$pedido_id = (int)$pedido['id'];

$lineas = Pedido::getProductosPedido($pedido_id);

$total = 0;

$formsActualizarHtml = [];
$formsEliminarHtml = [];

foreach ($lineas as $linea) {
  $total += $linea['precio_unitario'] * $linea['cantidad'];
  $prod_id = (int)$linea['producto_id'];
  
  // Instanciamos formularios de cada fila
  $formUpdate = new \es\ucm\fdi\aw\Formulario\FormularioActualizarLineaPedido($pedido_id, $prod_id, (int)$linea['cantidad']);
  $formsActualizarHtml[$prod_id] = $formUpdate->gestiona();

  $formRemove = new \es\ucm\fdi\aw\Formulario\FormularioEliminarLineaPedido($pedido_id, $prod_id);
  $formsEliminarHtml[$prod_id] = $formRemove->gestiona();
}
$total = round($total, 2);

// Instanciamos el de cancelar pedido general
$formCancelar = new \es\ucm\fdi\aw\Formulario\FormularioCancelarPedido($pedido_id);
$htmlFormCancelar = $formCancelar->gestiona();

// ---- EMPIEZA LA VISTA ----
$tituloPagina = 'Mi carrito | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>

  <?php foreach (flash_get_all() as $f): ?>
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
              <th>Imagen</th>
              <th>Producto</th>
              <th>Precio ud.</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lineas as $linea): 
              $prod_id = (int)$linea['producto_id'];
            ?>
              <tr>
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
                  <!-- Formulario Actualizar -->
                  <?= $formsActualizarHtml[$prod_id] ?>
                </td>
                <td><?= round($linea['precio_unitario'] * $linea['cantidad'], 2) ?> €</td>
                <td>
                  <!-- Formulario Eliminar -->
                  <?= $formsEliminarHtml[$prod_id] ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" style="text-align: right; padding-right: 15px;"><strong>Total:</strong></td>
              <td colspan="2"><strong><?= $total ?> €</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="actions-inline" style="margin-top:16px;">
        <a href="catalogo.php" class="btn">← Seguir añadiendo</a>
        
        
        <a href="../ofertas/ofertaCliente.php" class="btn">Ofertas</a>
        <a href="pago.php" class="btn primary">Confirmar pedido →</a>

        <div style="display:inline-block;">
          <?= $htmlFormCancelar ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>