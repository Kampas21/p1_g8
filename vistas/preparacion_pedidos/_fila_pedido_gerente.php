<?php
$pedido_id = (int)$p['id'];
$productos = PedidoService::getProductosPedido($pedido_id);
$pedido_cerrado = in_array($p['estado'], ['terminado', 'entregado'], true);
?>

<tr class="tabla-panel-fila">
    <td data-label="ID">
        <strong>#<?= (int)$p['id'] ?></strong>
    </td>

    <td data-label="Estado">
        <span class="badge-estado estado-<?= escaparHtml($p['estado']) ?>">
            <?= escaparHtml(strtoupper(str_replace('_', ' ', $p['estado']))) ?>
        </span>
    </td>

    <td data-label="Cocinero asignado" class="celda-centrada">
        <?php if ($p['cocinero_nombre']): ?>
            <?php
            $nombreCompleto = trim($p['cocinero_nombre'] . ' ' . $p['cocinero_apellidos']);

            $avatarEmergencia = 'https://ui-avatars.com/api/?name='
                . urlencode($nombreCompleto)
                . '&background=random&color=fff&rounded=true&size=100';

            if (!empty($p['avatar_valor'])) {
                $avatarImg = (
                    strpos($p['avatar_valor'], '/') === 0
                    || strpos($p['avatar_valor'], 'http') === 0
                )
                    ? $p['avatar_valor']
                    : RUTA_APP . '/' . $p['avatar_valor'];
            } else {
                $avatarImg = $avatarEmergencia;
            }
            ?>

            <div class="celda-flex-centro">
                <img src="<?= escaparHtml($avatarImg) ?>"
                     alt="Avatar"
                     onerror="this.onerror=null; this.src='<?= escaparHtml($avatarEmergencia) ?>';"
                     class="avatar-empleado">

                <span class="texto-destacado">
                    <?= escaparHtml($nombreCompleto) ?>
                </span>
            </div>
        <?php else: ?>
            <div class="celda-flex-centro">
                <div class="avatar-placeholder">
                    🕒
                </div>

                <span class="texto-gris-cursiva">
                    Sin asignar
                </span>
            </div>
        <?php endif; ?>
    </td>

    <td data-label="Camarero asignado" class="celda-centrada">
        <?php if (!empty($p['camarero_nombre'])): ?>
            <?php
            $nombreCompletoCamarero = trim($p['camarero_nombre'] . ' ' . $p['camarero_apellidos']);

            $avatarEmergenciaCamarero = 'https://ui-avatars.com/api/?name='
                . urlencode($nombreCompletoCamarero)
                . '&background=random&color=fff&rounded=true&size=100';

            if (!empty($p['camarero_avatar_valor'])) {
                $avatarImgCamarero = (
                    strpos($p['camarero_avatar_valor'], '/') === 0
                    || strpos($p['camarero_avatar_valor'], 'http') === 0
                )
                    ? $p['camarero_avatar_valor']
                    : RUTA_APP . '/' . $p['camarero_avatar_valor'];
            } else {
                $avatarImgCamarero = $avatarEmergenciaCamarero;
            }
            ?>

            <div class="celda-flex-centro">
                <img src="<?= escaparHtml($avatarImgCamarero) ?>"
                     alt="Avatar camarero"
                     onerror="this.onerror=null; this.src='<?= escaparHtml($avatarEmergenciaCamarero) ?>';"
                     class="avatar-empleado">

                <span class="texto-destacado">
                    <?= escaparHtml($nombreCompletoCamarero) ?>
                </span>
            </div>
        <?php else: ?>
            <div class="celda-flex-centro">
                <div class="avatar-placeholder">
                    🕒
                </div>

                <span class="texto-gris-cursiva">
                    Sin asignar
                </span>
            </div>
        <?php endif; ?>
    </td>
    
    <td data-label="Productos" class="celda-centrada">
        <ul class="lista-productos-gerente">
            <?php foreach ($productos as $prod): ?>
                <li>
                    <?= (int)$prod->getCantidad() ?>x
                    <?= escaparHtml($prod->getNombre()) ?>

                    <?= $prod->getSeCocina() ? '👨‍🍳' : '🤵' ?>

                    <?php if ($pedido_cerrado): ?>
                        🏁
                    <?php elseif ($prod->getEstado() === 'terminado'): ?>
                        🏁
                    <?php elseif ($prod->getEstado() === 'preparado'): ?>
                        ✅
                    <?php else: ?>
                        ⏳
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </td>
</tr>