<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/RecompensaDAO.php';

require_role('gerente');
$recompensas = RecompensaDAO::getAll(true);
$tituloPagina = 'Recompensas | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>
<div class="panel">
  <div class="actions-inline mb-12">
    <a href="crearRecompensa.php" class="btn primary">Nueva recompensa</a>
  </div>
  <h2>Gestión de recompensas</h2>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Producto</th>
          <th>Precio carta</th>
          <th>BistroCoins</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($recompensas as $recompensa): ?>
        <tr>
          <td><?= (int)$recompensa->getId() ?></td>
          <td><?= e($recompensa->getProductoNombre()) ?></td>
          <td><?= number_format($recompensa->getProductoPrecioFinal(), 2) ?> €</td>
          <td><?= (int)$recompensa->getBistrocoins() ?></td>
          <td><?= $recompensa->isActiva() ? 'Activa' : 'Inactiva' ?></td>
          <td class="actions-inline">
            <a class="btn small" href="recompensa_ver.php?id=<?= (int)$recompensa->getId() ?>">Ver</a>
            <a class="btn editar small" href="editarRecompensa.php?id=<?= (int)$recompensa->getId() ?>">Editar</a>
            <a class="btn borrar small" href="borrarRecompensa.php?id=<?= (int)$recompensa->getId() ?>" onclick="return confirm('¿Seguro que quieres borrar esta recompensa?');">Borrar</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
