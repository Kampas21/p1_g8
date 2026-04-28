<?php

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioAccionesCocina.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

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
    <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
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
                        <tr class="fila-pedido-cocinando">
                            <td class="celda-pedido-cocinando">
                                <h4 class="titulo-pedido-cocinando">Pedido #<?= $pedido_id ?> (<?= strtoupper($p['tipo']) ?>)</h4>
                                
                                <ul class="lista-platos">
                                    <?php 
                                    $productos = PedidoService::getProductosPedido($pedido_id);
                                    // Filtrar solo los productos que deben cocinarse
                                    $productos = array_filter($productos, function($pr){ return $pr->getSeCocina(); });
                                    $todosPreparados = true; 
                                    $hayProductos = count($productos) > 0;

                                    foreach ($productos as $prod): 
                                        $esPreparado = ($prod->getEstado() === 'preparado');
                                        if (!$esPreparado) { $todosPreparados = false; } 
                                    ?>
                                        <li class="item-plato">
                                            <span><strong><?= $prod->getCantidad() ?>x</strong> <?= htmlspecialchars($prod->getNombre()) ?></span>
                                            
                                            <?php if ($esPreparado): ?>
                                                <span class="estado-plato-listo">✅ Preparado</span>
                                            <?php else: 
                                                // Formulario Marcar Plato
                                                $formPlato = new \es\ucm\fdi\aw\Formulario\FormularioAccionesCocina($pedido_id, 'marcar_plato', $cocinero_id, (int)$prod->getProductoId() );
                                                echo $formPlato->gestiona();
                                            endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <?php if ($todosPreparados && $hayProductos): 
                                    // Formulario Finalizar Pedido
                                    $formFinalizar = new \es\ucm\fdi\aw\Formulario\FormularioAccionesCocina($pedido_id, 'finalizar', $cocinero_id);
                                    echo $formFinalizar->gestiona();
                                else: ?>
                                    <button class="btn-bloque-disabled" disabled title="Prepara todos los platos primero">
                                        ⏳ Faltan platos por preparar...
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
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