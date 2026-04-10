<?php
session_start();

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

require_once __DIR__ . '/../../entities/pedido.php';

require_once __DIR__ . '/../../includes/Formulario/FormularioActualizarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioEliminarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCancelarPedido.php';

require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/productoService.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = PedidoService::getPedidoNuevo($usuario_id);

if (!$pedido) {
  redirect('elegirTipo.php');
}

$pedido_id = (int)$pedido->getId();

$lineas = PedidoService::getProductosPedido($pedido_id);


$total = 0;

foreach ($lineas as $linea) {
  $total += $linea->getPrecio() * $linea->getCantidad();
}

$total = round($total, 2);


$descuento_total = 0;
$ofertas_aplicadas = [];

if (method_exists('PedidoService', 'getOfertasDePedido')) {
  $ofertas_aplicadas = PedidoService::getOfertasDePedido($pedido_id);

  foreach ($ofertas_aplicadas as $oferta) {
    $descuento_total += $oferta->descuento_total ?? $oferta['descuento_total'];
  }
}

$total_final = max(0, $total - $descuento_total);


$formsActualizarHtml = [];
$formsEliminarHtml = [];

foreach ($lineas as $linea) {

  $prod_id = (int)$linea->getProductoId();

  $formUpdate = new \es\ucm\fdi\aw\Formulario\FormularioActualizarLineaPedido(
    $pedido_id,
    $prod_id,
    (int)$linea->getCantidad()
  );

  $formsActualizarHtml[$prod_id] = $formUpdate->gestiona();

  $formRemove = new \es\ucm\fdi\aw\Formulario\FormularioEliminarLineaPedido(
    $pedido_id,
    $prod_id
  );

  $formsEliminarHtml[$prod_id] = $formRemove->gestiona();
}

$formCancelar = new \es\ucm\fdi\aw\Formulario\FormularioCancelarPedido($pedido_id);
$htmlFormCancelar = $formCancelar->gestiona();


$tituloPagina = 'Mi carrito | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<?php if (!empty($_SESSION['errores_ofertas'])): ?>

  <script>
    alert("⚠️ Algunas ofertas no se han podido aplicar:\n\n<?= implode('\n', array_map('addslashes', $_SESSION['errores_ofertas'])) ?>");
  </script>

  <?php unset($_SESSION['errores_ofertas']); ?>
<?php endif; ?>

<main>

  <?php foreach (flash_get_all() as $f): ?>
    <div class="mensaje-<?= e($f['type']) ?>">
      <?= e($f['message']) ?>
    </div>
  <?php endforeach; ?>

  <div class="panel">

    <h2>Mi carrito
      <span class="text-muted-italic">
        — pedido <?= e($pedido->getTipo() === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
      </span>
    </h2>

    <?php if (empty($lineas)): ?>
      <p>El carrito está vacío.</p>

    <?php else: ?>


      <?php if (!empty($ofertas_aplicadas)): ?>
        <div class="panel">
          <h3>🟢 Ofertas aplicadas</h3>

          <ul>
            <?php foreach ($ofertas_aplicadas as $o): ?>

              <li>

                <strong>
                  <?= e($o->nombre ?? $o['nombre']) ?>
                </strong>

                — <?= $o->veces_aplicada ?? $o['veces_aplicada'] ?>x
                — -<?= $o->descuento_total ?? $o['descuento_total'] ?> €

                
                <div style="margin-left:15px; font-size: 0.9em; color:#666;">

                  <?php
                 
                  $productos_oferta = ProductoService::getProductosDeOferta(
                    $o->oferta_id
                  );

                  foreach ($productos_oferta as $po):
                  ?>

                    • <?= e($po->getNombre()) ?> (x<?= $po->cantidad ?>)<br>

                  <?php endforeach; ?>

                </div>

              </li>

            <?php endforeach; ?>
          </ul>

        </div>
      <?php endif; ?>


      <div class="table-wrap">
        <table>

          <thead>
            <tr>
              <th>Producto</th>
              <th>Precio</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
            </tr>
          </thead>

          <tbody>

            <?php foreach ($lineas as $linea):
              $prod_id = (int)$linea->getProductoId();
            ?>

              <tr>

                <td><?= e($linea->getNombre()) ?></td>

                <td><?= e($linea->getPrecio()) ?> €</td>

                <td><?= $formsActualizarHtml[$prod_id] ?></td>

                <td><?= round($linea->getPrecio() * $linea->getCantidad(), 2) ?> €</td>

              </tr>

            <?php endforeach; ?>

          </tbody>

          <tfoot>

            <tr>
              <td colspan="3"><strong>Total:</strong></td>
              <td><strong><?= $total ?> €</strong></td>
            </tr>

            <tr>
              <td colspan="3"><strong>Descuento:</strong></td>
              <td><strong>-<?= round($descuento_total, 2) ?> €</strong></td>
            </tr>

            <tr>
              <td colspan="3"><strong>Total final:</strong></td>
              <td><strong><?= round($total_final, 2) ?> €</strong></td>
            </tr>

          </tfoot>

        </table>
      </div>


      <div class="actions-inline">

        <a href="catalogo.php" class="btn">Seguir comprando</a>

        <form action="../ofertas/ofertaCliente.php" method="POST">
          <input type="hidden" name="pedido_id" value="<?= $pedido_id ?>">
          <button class="btn-nuevo">Ofertas</button>
        </form>

        <a href="pago.php" class="btn primary">Confirmar</a>

      </div>

    <?php endif; ?>

  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>