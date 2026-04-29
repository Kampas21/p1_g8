<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioEstadoCamarero.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_role('camarero');

$recibidos     = PedidoService::getPedidosPorEstado('recibido');
$listos_cocina = PedidoService::getPedidosPorEstado('listo_cocina');
$terminados    = PedidoService::getPedidosPorEstado('terminado');

// Pestaña elegida por URL, 'recibidos' por defecto
$tab = $_GET['tab'] ?? 'recibidos';

$tituloPagina = 'Gestión Camarero | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2>Panel de Camarero — <?= e($user->getNombre()) ?></h2>
    
    <!-- Menú de Pestañas Diferenciadas -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="gestionCamarero.php?tab=recibidos" class="btn <?= $tab === 'recibidos' ? 'editar' : '' ?>">💰 Pendientes de cobro (<?= count($recibidos) ?>)</a>
        <a href="gestionCamarero.php?tab=listos" class="btn <?= $tab === 'listos' ? 'editar' : '' ?>">✅ Listos en cocina (<?= count($listos_cocina) ?>)</a>
        <a href="gestionCamarero.php?tab=entregar" class="btn <?= $tab === 'entregar' ? 'editar' : '' ?>">🛎️ Para entregar (<?= count($terminados) ?>)</a>
    </div>
</div>

<main>
    <?php if ($tab === 'recibidos'): ?>
    <!-- PESTAÑA: Recibidos (Cobrar) -->
    <section class="columna">
        <div class="columna-header recibido">
          <span>💰 Pedidos Recibidos (Pendiente de cobro)</span>
        </div>
        <div class="columna-body" style="flex-direction: row; flex-wrap: wrap;">
            <?php 
                $pedidos = $recibidos; 
                $accion = 'cobrar'; 
                include __DIR__ . '/_tarjetas_camarero.php'; 
            ?>
        </div>
    </section>
    <?php endif; ?>

    
    <?php if ($tab === 'listos'): ?>
    <!-- PESTAÑA: Listos para recoger de cocina -->
    <section class="columna">
        <div class="columna-header listo">
          <span>✅ Listos en cocina</span>
        </div>
        <div class="columna-body" style="flex-direction: row; flex-wrap: wrap;">
            <?php 
                $pedidos = $listos_cocina; 
                $accion = 'preparar_barra'; 
                include __DIR__ . '/_tarjetas_camarero.php'; 
            ?>
        </div>
    </section>
    <?php endif; ?>

    
    <?php if ($tab === 'entregar'): ?>
    <!-- PESTAÑA: Terminados y preparados para el cliente -->
    <section class="columna">
        <div class="columna-header terminado">
          <span>🛎️ Para entregar al Cliente</span>
        </div>
        <div class="columna-body" style="flex-direction: row; flex-wrap: wrap;">
            <?php 
                $pedidos = $terminados; 
                $accion = 'entregar'; 
                include __DIR__ . '/_tarjetas_camarero.php'; 
            ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>