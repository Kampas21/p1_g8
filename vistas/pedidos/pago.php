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

if (!PedidoService::carritoTieneTipo()) {
    redirect('elegirTipo.php');
}

$lineas = PedidoService::getCarritoItems();
if (empty($lineas)) {
    redirect('carrito.php');
}

// 1. Calculamos el dinero de los platos puros
$total_sin_descuentos = PedidoService::calcularTotalCarritoSinDescuentos();

// 2. Comprobamos si tiene ofertas aplicadas en el carrito y se las restamos
$total_descuento = PedidoService::calcularDescuentoCarrito();

// 3. Calculamos cuánto le toca pagar exactamente al cliente
$total = max(0, round($total_sin_descuentos - $total_descuento, 2));

// [!Opcional pero recomendado!] Guardamos este total de forma oficial en la BD
// Se guardará al confirmar el pedido.


// Instanciamos el formulario y le pasamos los datos necesarios
$form = new \es\ucm\fdi\aw\Formulario\FormularioPago($usuario_id, $total_sin_descuentos, $total_descuento, $total);
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
