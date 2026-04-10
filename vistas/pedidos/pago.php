<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioPago.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = PedidoService::getPedidoNuevo($usuario_id);
if (!$pedido) {
    redirect('elegirTipo.php');
}
$pedido_id = $pedido->getId();

$lineas = PedidoService::getProductosPedido($pedido_id);
if (empty($lineas)) {
    redirect('carrito.php');
}

// $total = 0;
// foreach ($lineas as $linea) {
//     $total += $linea->getPrecio() * $linea->getCantidad();
// }
// $total = round($total, 2);

$total=$pedido->getTotal();

// Instanciamos el formulario y le pasamos los datos necesarios
$form = new \es\ucm\fdi\aw\Formulario\FormularioPago($pedido_id, $total);
$htmlForm = $form->gestiona();

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Pago del pedido | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <div class="panel">
    <div class="actions-inline mb-12">
      <a href="carrito.php" class="btn">← Volver al carrito</a>
    </div>

    <h2>Pago</h2>
    <p>Total a pagar: <strong><?= $total ?> €</strong></p>
  </div>

  <?= $htmlForm ?>

</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>
