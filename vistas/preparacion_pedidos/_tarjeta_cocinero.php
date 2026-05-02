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
