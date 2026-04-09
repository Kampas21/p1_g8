<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioElegirTipo.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_login();
$usuario_id = (int)$user->getId();

// Si ya tiene un carrito/pedido activo, lo mandamos al carrito
$pedidoActivo = PedidoService::getPedidoNuevo($usuario_id);
if ($pedidoActivo) {
    redirect('carrito.php');
}

// Instanciamos el nuevo formulario
$form = new \es\ucm\fdi\aw\Formulario\FormularioElegirTipo($usuario_id);
$htmlForm = $form->gestiona();

$tituloPagina = 'Elegir tipo de pedido | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <?php foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <h2>¿Cómo quieres tu pedido?</h2>
    <?= $htmlForm ?>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';