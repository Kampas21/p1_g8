<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

$user = require_login();
$usuarioIdConsulta = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : (int)$user->getId();

if ($usuarioIdConsulta !== (int)$user->getId() && !user_has_role($user, 'gerente')) {
  http_response_code(403);
  flash_set('error', 'No tienes permiso para ver los pedidos de otro usuario.');
  redirect('listarPedidosCliente.php');
}

$usuarioConsulta = UsuarioDAO::user_find_by_id($usuarioIdConsulta);
if (!$usuarioConsulta) {
  flash_set('error', 'Usuario no encontrado.');
  redirect('listarPedidosCliente.php');
}

$pedidos = PedidoService::getPedidosDeUsuario($usuarioIdConsulta);
$esGerenteConsultando = $usuarioIdConsulta !== (int)$user->getId();

$etiquetas = [
    'recibido'       => 'Pendiente de pago',
    'en_preparacion' => 'En preparación',
    'cocinando'      => 'Cocinando',
    'listo_cocina'   => 'Listo en cocina',
    'terminado'      => 'Listo para recoger',
    'entregado'      => 'Entregado',
    'cancelado'      => 'Cancelado',
];

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Mis pedidos | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <?php 
  // Mostrar mensajes flash
  foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= escaparHtml($f['type']) ?>"><?= escaparHtml($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <div class="actions-inline flex-between">
      <h2 class="m-0">
        <?= $esGerenteConsultando ? 'Pedidos de ' . escaparHtml($usuarioConsulta->getNombreCompleto()) : 'Mis pedidos' ?>
      </h2>
      <div class="actions-inline">
        <?php if (user_has_role($user, 'gerente')): ?>
          <a href="../usuarios/usuario_ver.php?id=<?= $usuarioIdConsulta ?>" class="btn">← Volver al usuario</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (empty($pedidos)): ?>
    <div class="panel">
      <p><?= $esGerenteConsultando ? 'Este usuario todavía no tiene pedidos.' : 'Todavía no tienes pedidos.' ?></p>
      <?php if (user_has_role($user, 'gerente')): ?>
        <a href="../usuarios/usuario_ver.php?id=<?= $usuarioIdConsulta ?>" class="btn">← Volver al usuario</a>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <div class="panel">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nº pedido</th>
              <th>Fecha</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($pedidos as $p): ?>
            <tr>
              <td><strong>#<?= escaparHtml($p->getNumero_pedido()) ?></strong></td>
              <td><?= escaparHtml(substr($p->getFecha_hora(), 0, 16)) ?></td>
              <td><?= $p->getTipo() === 'local' ? '🍽️ Local' : '🥡 Llevar' ?></td>
              <td><?= escaparHtml($etiquetas[$p->getEstado()] ?? $p->getEstado()) ?></td>
              <td><?= escaparHtml($p->setTotal()) ?> €</td>
              <td>
                <a href="estadoPedido.php?id=<?= (int)$p->getId() ?><?= $esGerenteConsultando ? '&usuario_id=' . $usuarioIdConsulta : '' ?>" class="btn small">
                  Ver detalle
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>
