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

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = PedidoService::getPedidoNuevo($usuario_id);

if (!$pedido) {
  redirect('elegirTipo.php');
}
<<<<<<< HEAD

// 🔥 CORRECCIÓN CLAVE
$pedido_id = (int)$pedido->getId();
$tipoPedido = $pedido->getTipo();
=======
$pedido_id = (int)$pedido->getId();
>>>>>>> 5909c886496791a99285c8e3d964836d9ea67fb1

$lineas = PedidoService::getProductosPedido($pedido_id);

$total = 0;

$formsActualizarHtml = [];
$formsEliminarHtml = [];

foreach ($lineas as $linea) {
  $total += $linea['precio_unitario'] * $linea['cantidad'];
  $prod_id = (int)$linea['producto_id'];
<<<<<<< HEAD
  
=======

  // Instanciamos formularios de cada fila
>>>>>>> 5909c886496791a99285c8e3d964836d9ea67fb1
  $formUpdate = new \es\ucm\fdi\aw\Formulario\FormularioActualizarLineaPedido($pedido_id, $prod_id, (int)$linea['cantidad']);
  $formsActualizarHtml[$prod_id] = $formUpdate->gestiona();

  $formRemove = new \es\ucm\fdi\aw\Formulario\FormularioEliminarLineaPedido($pedido_id, $prod_id);
  $formsEliminarHtml[$prod_id] = $formRemove->gestiona();
}

$total = round($total, 2);

$formCancelar = new \es\ucm\fdi\aw\Formulario\FormularioCancelarPedido($pedido_id);
$htmlFormCancelar = $formCancelar->gestiona();

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
<<<<<<< HEAD
        — pedido <?= e($tipoPedido === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
=======
        — pedido <?= e($pedido->getTipo() === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
>>>>>>> 5909c886496791a99285c8e3d964836d9ea67fb1
      </span>
    </h2>

    <?php if (empty($lineas)): ?>
      <p>El carrito está vacío.</p>
<a href="../catalogo.php" class="btn">← Volver al catálogo</a>
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
                <td class="text-center">
                  <?php if (!empty($linea['imagen'])): ?>
                    <img src="<?= RUTA_APP . '/' . e($linea['imagen']) ?>" alt="<?= e($linea['nombre']) ?>" class="img-thumbnail">
                  <?php else: ?>
                    <span class="text-muted-italic">(Sin imagen)</span>
                  <?php endif; ?>
                </td>

                <td><?= e($linea['nombre']) ?></td>
                <td><?= e($linea['precio_unitario']) ?> €</td>

                <td>
                  <?= $formsActualizarHtml[$prod_id] ?>
                </td>

                <td><?= round($linea['precio_unitario'] * $linea['cantidad'], 2) ?> €</td>

                <td>
                  <?= $formsEliminarHtml[$prod_id] ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>

          <tfoot>
            <tr>
              <td colspan="4" class="text-right pr-15"><strong>Total:</strong></td>
              <td colspan="2"><strong><?= $total ?> €</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="actions-inline mt-16">
<<<<<<< HEAD
        <a href="../catalogo.php" class="btn">← Seguir añadiendo</a>
        <a href="../ofertas/ofertaCliente.php" class="btn">Ofertas</a>
=======
        <a href="catalogo.php" class="btn">← Seguir añadiendo</a>

        <form action="../ofertas/ofertaCliente.php" method="POST" style="display:inline;">
          <input type="hidden" name="pedido_id" value="<?= (int)$pedido_id ?>">
          <button type="submit" class="btn-nuevo">Ofertas</button>
        </form>

>>>>>>> 5909c886496791a99285c8e3d964836d9ea67fb1
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