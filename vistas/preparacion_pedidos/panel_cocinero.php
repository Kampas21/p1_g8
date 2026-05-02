<?php

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioAccionesCocina.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

$user = require_role('cocinero'); 
$cocinero_id = (int)$user->getId();

$pedidosEnCola = PedidoService::getPedidosPorEstado('en_preparacion'); 
$misPedidos = PedidoService::getPedidosCocinando($cocinero_id);

// Definir pestaña activa (por defecto 'mis_pedidos')
$tab = $_GET['tab'] ?? 'mis_pedidos';

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Panel de Cocina | Bistro FDI';
ob_start();
?>

<header class="panel">
    <h2>👨‍🍳 Panel de Cocina - <?= htmlspecialchars($user->getNombre()) ?></h2>
    
    <!-- Menú de Pestañas Diferenciadas -->
    <div class="tab-bar">
        <a href="panel_cocinero.php?tab=mis_pedidos" class="btn <?= $tab === 'mis_pedidos' ? 'editar' : '' ?>">🔥 Mis Pedidos (<?= count($misPedidos) ?>)</a>
        <a href="panel_cocinero.php?tab=en_cola" class="btn <?= $tab === 'en_cola' ? 'editar' : '' ?>">📋 Pedidos en Cola (<?= count($pedidosEnCola) ?>)</a>
    </div>
</header>

<?php if ($tab === 'mis_pedidos'): ?>
    <section class="panel panel-cocinando">
        <h3>🔥 Mis Pedidos (Cocinando)</h3>
        <div class="table-wrap">
            <table class="tabla-panel">
                <tbody>
                <?php if (empty($misPedidos)): ?>
                    <tr>
                        <td class="tabla-panel-vacia">No estás cocinando ningún pedido ahora mismo.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($misPedidos as $p): 
                        $pedido_id = (int)$p['id'];
                    ?>
                        <?php require __DIR__ . '/_tarjeta_cocinero.php'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php if ($tab === 'en_cola'): ?>
    <section class="panel panel-esperando">
        <h3>📋 Pedidos en Cola (Esperando)</h3>
        <div class="table-wrap">
            <table class="tabla-panel">
                <tbody>
                <?php if (empty($pedidosEnCola)): ?>
                    <tr><td class="tabla-panel-vacia">No hay pedidos esperando en la cola.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidosEnCola as $p): 
                        $pedido_id = (int)$p['id'];
                    ?>
                        <tr class="tabla-panel-fila">
                            <td><strong>Pedido #<?= $pedido_id ?></strong></td>
                            <td class="text-right">
                                <?php 
                                    // Formulario Tomar Pedido (de la cola a mis pedidos)
                                    $formTomar = new \es\ucm\fdi\aw\Formulario\FormularioAccionesCocina($pedido_id, 'tomar', $cocinero_id);
                                    echo $formTomar->gestiona();
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>