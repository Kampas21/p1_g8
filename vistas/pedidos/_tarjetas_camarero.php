<?php 
// SCRIPT DE APOYO: Sólo recibe $pedidos y $accion por scope y pinta
if (empty($pedidos)): 
?>
    <p class="text-muted-italic" style="grid-column: 1 / -1;">No hay pedidos en esta zona.</p>
<?php 
else: 
    foreach ($pedidos as $p): 
        $pedido_id = (int)$p['id'];
        $formObj = new \es\ucm\fdi\aw\Formulario\FormularioEstadoCamarero($pedido_id, $accion);
        $htmlForm = $formObj->gestiona();
?>
        <div class="pedido-card">
            <div class="pedido-card-header">
                <span class="pedido-num">#<?= (int)$p['numero_pedido'] ?></span>
                <span><?= $p['tipo'] === 'local' ? '🍽️ Local' : '🥡 Llevar' ?></span>
            </div>

            <div class="pedido-card-body">
                <p><strong>Cliente:</strong> <?= e($p['cliente_nombre']) ?></p>
                <p><strong>Hora:</strong> <?= e(substr($p['fecha_hora'], 11, 5)) ?></p>
                <p><strong>Total:</strong> <?= e($p['total']) ?> €</p>
            </div>

            <?php
                // Mostrar solo los productos que no se cocinan (barra)
                $productos = 
                    \PedidoService::getProductosPedido($pedido_id);
                $productos_barra = array_filter($productos, function($pr){ return !$pr->getSeCocina(); });
                if (!empty($productos_barra)): ?>
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
        </div>
<?php 
    endforeach; 
endif; 
?>