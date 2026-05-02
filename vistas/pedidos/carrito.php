<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

require_once __DIR__ . '/../../entities/pedido.php';

require_once __DIR__ . '/../../includes/Formulario/FormularioActualizarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioEliminarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCancelarPedido.php';

require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';

$user = require_login();
if (!PedidoService::carritoTieneTipo()) {
  flash_set('info', 'No tienes carrito. Inicia un pedido para añadir productos.');
  redirect('elegirTipo.php');
}

$lineas = PedidoService::getCarritoItems();
$tipoPedido = PedidoService::getTipoCarrito();
$total = PedidoService::calcularTotalCarritoSinDescuentos();
$descuento_total = PedidoService::calcularDescuentoCarrito();
$ofertas_aplicadas = PedidoService::getCarritoOfertas();
$total_final = max(0, round($total - $descuento_total, 2));

$bistrocoins_reservados = 0;
foreach ($lineas as $item) {
    if (!empty($item['es_recompensa'])) {
        $bistrocoins_reservados += ((int)$item['bistrocoins_unitarios']) * ((int)$item['cantidad']);
    }
}

$ofertas_ids = $_SESSION['ofertas_seleccionadas'] ?? [];
OfertaService::aplicarOfertas($ofertas_ids);

$formsActualizarHtml = [];
$formsEliminarHtml = [];

foreach ($lineas as $clave => $item) {
  $prod_id = isset($item['producto_id']) ? (int)$item['producto_id'] : (int)$clave;
  
  if (empty($item['es_recompensa'])) {
      $formUpdate = new \es\ucm\fdi\aw\Formulario\FormularioActualizarLineaPedido(
        $prod_id,
        (int)($item['cantidad'] ?? 1)
      );

      $formsActualizarHtml[$clave] = $formUpdate->gestiona();
  }

  $formRemove = new \es\ucm\fdi\aw\Formulario\FormularioEliminarLineaPedido(
    $clave
  );

  $formsEliminarHtml[$clave] = $formRemove->gestiona();
}

$formCancelar = new \es\ucm\fdi\aw\Formulario\FormularioCancelarPedido();
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
    <div class="mensaje-<?= escaparHtml($f['type']) ?>">
      <?= escaparHtml($f['message']) ?>
    </div>
  <?php endforeach; ?>

  <div class="panel">

    <h2>Mi carrito
      <span class="text-muted-italic">
        — pedido <?= escaparHtml($tipoPedido === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
      </span>
    </h2>

    <?php if (empty($lineas)): ?>
      <p>El carrito está vacío.</p>

      <div class="actions-inline">

        <a href="catalogo.php" class="btn">← Seguir comprando</a>
        <a href="<?= RUTA_APP ?>/vistas/recompensas/recompensaCliente.php" class="btn-nuevo">Recompensas</a>

        <?= $htmlFormCancelar ?>

      </div>

    <?php else: ?>


      <?php if (!empty($ofertas_aplicadas)): ?>
        <div class="panel">
          <h3>🟢 Ofertas aplicadas</h3>

          <ul>
            <?php foreach ($ofertas_aplicadas as $o): ?>

              <li>

                <strong>
                  <?= escaparHtml($o['nombre'] ?? '') ?>
                </strong>

                — <?= (int)($o['veces_aplicada'] ?? 0) ?>x
                — -<?= round((float)($o['descuento_total'] ?? 0), 2) ?> €


                <div class="oferta-detalle">

                  <?php

                  $productos_oferta = ProductoDAO::getProductosDeOferta(
                    $o['oferta_id']
                  );

                  foreach ($productos_oferta as $po):
                  ?>

                    • <?= escaparHtml($po->getNombre()) ?> (x<?= $po->cantidad ?>)<br>

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
              <th>Imagen</th>
              <th>Producto</th>
              <th>Precio</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
            </tr>
          </thead>

          <tbody>

            <?php foreach ($lineas as $clave => $item):
              $prod_id = isset($item['producto_id']) ? (int)$item['producto_id'] : (int)$clave;
              $producto = ProductoDAO::getById($prod_id);
              if (!$producto) { continue; }
              $cantidad = (int)($item['cantidad'] ?? 1);
              $precio = (float)($item['precio_unitario'] ?? 0);
              $esRecompensa = !empty($item['es_recompensa']);
              $bistrocoinsUnitarios = (int)($item['bistrocoins_unitarios'] ?? 0);
            ?>

              <tr>

                <td>
                  <img src="<?= escaparHtml(RUTA_APP . '/' . $producto->getImagen()) ?>" class="img-thumbnail" alt="<?= escaparHtml($producto->getNombre()) ?>">
                </td>

                <td>
                  <a href="<?= RUTA_APP ?>/vistas/productos/detalle_producto.php?id=<?= $producto->getId() ?>" class="click">
                    <?= escaparHtml($producto->getNombre()) ?>
                  </a>
                  <?php if ($esRecompensa): ?>
                      <br><small><strong>(Recompensa)</strong></small>
                  <?php endif; ?>
                </td>

                <td class="col-precio">
                    <?php if ($esRecompensa): ?>
                        <?= escaparHtml((string)$bistrocoinsUnitarios) ?> BC
                    <?php else: ?>
                        <?= escaparHtml((string)$precio) ?> €
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($esRecompensa): ?>
                        <?= escaparHtml((string)$cantidad) ?>
                    <?php else: ?>
                        <?= $formsActualizarHtml[$clave] ?? escaparHtml((string)$cantidad) ?>
                    <?php endif; ?>
                </td>

                <td class="col-precio">
                    <?php if ($esRecompensa): ?>
                        <?= escaparHtml((string)($bistrocoinsUnitarios * $cantidad)) ?> BC
                    <?php else: ?>
                        <?= round($precio * $cantidad, 2) ?> €
                    <?php endif; ?>
                </td>
                
                <td>
                    <?= $formsEliminarHtml[$clave] ?? '' ?>
                </td>

              </tr>

            <?php endforeach; ?>

          </tbody>

          <tfoot>

            <tr>
              <td colspan="4"><strong>Total productos de pago:</strong></td>
              <td class="col-precio"><strong><?= $total ?> €</strong></td>
            </tr>

            <?php if ($descuento_total > 0): ?>
              <tr>
                <td colspan="4"><strong>Descuento:</strong></td>
                <td class="col-precio"><strong>-<?= round($descuento_total, 2) ?> €</strong></td>
              </tr>

              <tr>
                <td colspan="4"><strong>Total a pagar:</strong></td>
                <td class="col-precio"><strong><?= round($total_final, 2) ?> €</strong></td>
              </tr>
            <?php endif; ?>

            <?php if ($bistrocoins_reservados > 0): ?>
              <tr>
                <td colspan="4"><strong>BistroCoins reservados:</strong></td>
                <td class="col-precio"><strong><?= escaparHtml((string)$bistrocoins_reservados) ?> BC</strong></td>
              </tr>
            <?php endif; ?>

          </tfoot>

        </table>
      </div>


      <div class="actions-inline">

        <a href="catalogo.php" class="btn">← Seguir comprando</a>

        <a href="../ofertas/ofertaCliente.php?modo=edicion" class="btn-nuevo">Ofertas</a>

        <a href="<?= RUTA_APP ?>/vistas/recompensas/recompensaCliente.php" class="btn-nuevo">Recompensas</a>

        <a href="pago.php" class="btn primary">Confirmar</a>

        <?= $htmlFormCancelar ?>

      </div>

    <?php endif; ?>

  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>