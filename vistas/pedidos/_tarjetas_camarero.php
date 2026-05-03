<?php 
// SCRIPT DE APOYO: Sólo recibe $pedidos y $accion por scope y pinta
if (empty($pedidos)): 
?>
    <p class="text-muted-italic span-full">No hay pedidos en esta zona.</p>
<?php 
else: 
    foreach ($pedidos as $p): 
        $pedido_id = (int)$p['id'];
        $pedido_cerrado = in_array($p['estado'], ['terminado', 'entregado'], true);
        $formObj = new \es\ucm\fdi\aw\Formulario\FormularioAccionesCamarero($pedido_id, $accion, $user->getId());
        $htmlForm = $formObj->gestiona();
?>
        <div class="pedido-card">
            <div class="pedido-card-header">
                <span class="pedido-num">#<?= (int)$p['numero_pedido'] ?></span>
                <span><?= $p['tipo'] === 'local' ? '🍽️ Local' : '🥡 Llevar' ?></span>
            </div>

            <div class="pedido-card-body">
                <p><strong>Cliente:</strong> <?= escaparHtml($p['cliente_nombre']) ?></p>
                <p><strong>Hora:</strong> <?= escaparHtml(substr($p['fecha_hora'], 11, 5)) ?></p>
                <p><strong>Total:</strong> <?= escaparHtml($p['total']) ?> €</p>
            </div>

            <?php
                $productos = \PedidoService::getProductosPedido($pedido_id);
                $productos_barra = array_values(array_filter($productos, function($pr){ return !$pr->getSeCocina(); }));
            ?>

            <?php if ($accion === 'preparar_barra'): ?>
                <div class="pedido-card-body">
                    <p><strong>Productos de barra:</strong></p>
                    <?php if (empty($productos_barra)): ?>
                        <p class="text-muted-italic">No hay productos de barra pendientes.</p>
                        <?php $formListo = new \es\ucm\fdi\aw\Formulario\FormularioAccionesCamarero($pedido_id, 'pasar_entrega', $user->getId()); ?>
                        <?= $formListo->gestiona() ?>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($productos_barra as $pb): ?>
                                <li>
                                    <?= (int)$pb->getCantidad() ?>x <?= htmlspecialchars($pb->getNombre()) ?>
                                    <?php if ($pb->getEstado() === 'terminado'): ?>
                                        <span class="estado-plato-listo">🏁 Terminado</span>
                                    <?php elseif ($pb->getEstado() === 'preparado'): ?>
                                        <span class="estado-plato-listo">✅ Preparado</span>
                                    <?php else: ?>
                                        <?php $formLinea = new \es\ucm\fdi\aw\Formulario\FormularioAccionesCamarero($pedido_id, 'preparar_linea', $user->getId(), (int)$pb->getProductoId()); ?>
                                        <?= $formLinea->gestiona() ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php elseif ($accion === 'entregar'): ?>
                <div class="pedido-card-body">
                    <p><strong>Productos terminados:</strong></p>
                    <ul>
                        <?php foreach ($productos as $pr): ?>
                            <li>
                                <?= (int)$pr->getCantidad() ?>x <?= htmlspecialchars($pr->getNombre()) ?>
                                <span class="estado-plato-listo">🏁 <?= $pedido_cerrado ? 'Terminado' : ($pr->getEstado() === 'terminado' ? 'Terminado' : 'Pendiente') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?= $htmlForm ?>
            <?php else: ?>
                <?php if (!empty($productos_barra)): ?>
                    <div class="pedido-card-body">
                        <p><strong>Para preparar (barra):</strong></p>
                        <ul>
                            <?php foreach ($productos_barra as $pb): ?>
                                <li><?= (int)$pb->getCantidad() ?>x <?= htmlspecialchars($pb->getNombre()) ?> <?= $pb->getEstado() === 'preparado' ? '✅' : '⏳' ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?= $htmlForm ?>
            <?php endif; ?>
        </div>
<?php 
    endforeach; 
endif; 
?>