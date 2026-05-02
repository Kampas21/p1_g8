<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/RecompensaDAO.php';
require_role('gerente');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); die('ID inválido'); }
$recompensa = RecompensaDAO::getById($id);
if (!$recompensa) { http_response_code(404); die('Recompensa no encontrada'); }
$tituloPagina = 'Detalle recompensa | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>
<div class="panel">
  <h2>Detalle de recompensa</h2>
  <p><strong>ID:</strong> <?= (int)$recompensa->getId() ?></p>
  <p><strong>Producto:</strong> <?= escaparHtml($recompensa->getProductoNombre()) ?></p>
  <p><strong>Descripción:</strong> <?= escaparHtml($recompensa->getProductoDescripcion()) ?></p>
  <p><strong>Precio en carta:</strong> <?= number_format($recompensa->getProductoPrecioFinal(), 2) ?> €</p>
  <p><strong>BistroCoins necesarias:</strong> <?= (int)$recompensa->getBistrocoins() ?></p>
  <p><strong>Estado:</strong> <?= $recompensa->isActiva() ? 'Activa' : 'Inactiva' ?></p>
  <div class="actions-inline mt-16">
    <a class="btn editar" href="editarRecompensa.php?id=<?= (int)$recompensa->getId() ?>">Editar</a>
    <a class="btn-volver" href="listarRecompensas.php">Volver</a>
  </div>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
