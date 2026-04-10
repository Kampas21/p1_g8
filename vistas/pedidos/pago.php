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

// 1. Calculamos el dinero de los platos puros
$total_sin_descuentos = 0;
foreach ($lineas as $linea) {
    $total_sin_descuentos += $linea->getPrecio() * $linea->getCantidad();
}

// 2. Comprobamos si tiene ofertas aplicadas en el carrito y se las restamos
$total_descuento = 0;
if (method_exists('PedidoService', 'getOfertasDePedido')) {
    $ofertas_aplicadas = PedidoService::getOfertasDePedido($pedido_id);
    foreach ($ofertas_aplicadas as $oferta) {
        $total_descuento += $oferta->descuento_total ?? $oferta['descuento_total'];
    }
}

// 3. Calculamos cuánto le toca pagar exactamente al cliente
$total = max(0, $total_sin_descuentos - $total_descuento);
$total = round($total, 2);

// [!Opcional pero recomendado!] Guardamos este total de forma oficial en la BD
PedidoService::actualizarTotales($pedido_id, $total_sin_descuentos, $total_descuento);


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
