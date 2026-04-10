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
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = PedidoService::getPedidoNuevo($usuario_id);
if (!$pedido) {
  redirect('elegirTipo.php');
}
$pedido_id = (int)$pedido->getId();

$lineas = PedidoService::getProductosPedido($pedido_id);

$total = 0;

$formsActualizarHtml = [];
$formsEliminarHtml = [];

foreach ($lineas as $linea) {
  $total += $linea->getPrecio() * $linea->getCantidad();
  $prod_id = (int)$linea->getProductoId();

  // Instanciamos formularios de cada fila
  $formUpdate = new \es\ucm\fdi\aw\Formulario\FormularioActualizarLineaPedido($pedido_id, $prod_id, (int)$linea->getCantidad());
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
      <span class="text-muted-italic">
        — pedido <?= e($pedido->getTipo() === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
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
              $prod_id = (int)$linea->getProductoId();
            ?>
              <tr>
                <td class="text-center">
                  <?php if (!empty($linea->getImagen())): ?>
                    <img src="<?= RUTA_APP . '/' . e($linea->getImagen()) ?>" alt="<?= e($linea->getNombre()) ?>" class="img-thumbnail">
                  <?php else: ?>
                    <span class="text-muted-italic">(Sin imagen)</span>
                  <?php endif; ?>
                </td>

                <td><?= e($linea->getNombre()) ?></td>
                <td><?= e($linea->getPrecio()) ?> €</td>
                <td>
                  <!-- Formulario Actualizar -->
                  <?= $formsActualizarHtml[$prod_id] ?>
                </td>
                <td><?= round($linea->getPrecio() * $linea->getCantidad(), 2) ?> €</td>
                <td>
                  <!-- Formulario Eliminar -->
                  <?= $formsEliminarHtml[$prod_id] ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-right" class="pr-15"><strong>Total:</strong></td>
              <td colspan="2"><strong><?= $total ?> €</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="actions-inline mt-16">
        <a href="catalogo.php" class="btn">← Seguir añadiendo</a>

        <form action="../ofertas/ofertaCliente.php" method="POST" style="display:inline;">
          <input type="hidden" name="pedido_id" value="<?= (int)$pedido_id ?>">
          <button type="submit" class="btn-nuevo">Ofertas</button>
        </form>

        <a href="pago.php" class="btn primary">Confirmar pedido →</a>

        <div class="inline-block">
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