<?php

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_role('gerente');
$pedidos = PedidoService::getPedidosPendientesGerente();

$tituloPagina = 'Panel de Gerencia | Bistro FDI';

ob_start();
?>

<div class="panel">
    <h2>👔 Visión Global de Gerencia</h2>
    <p>
        Usuario: <?= escaparHtml($user->getNombre()) ?>
         (<?= escaparHtml(ucfirst((string) $user->getRol())) ?>)
    </p>
</div>

<div class="panel">
    <h3>📊 Todos los Pedidos Pendientes</h3>

    <div class="table-wrap">
        <table class="tabla-panel tabla-movil">
            <thead>
                <tr class="tabla-panel-cabecera">
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Cocinero Asignado</th>
                    <th>Camarero Asignado</th>
                    <th>Productos</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="5" class="tabla-panel-vacia" data-label="">
                            No hay pedidos pendientes en este momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pedidos as $p): ?>
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

                            <td data-label="Cocinero asignado" class="celda-flex">
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

                                    <img src="<?= escaparHtml($avatarImg) ?>"
                                         alt="Avatar"
                                         onerror="this.onerror=null; this.src='<?= escaparHtml($avatarEmergencia) ?>';"
                                         class="avatar-empleado">

                                    <span class="texto-destacado">
                                        <?= escaparHtml($nombreCompleto) ?>
                                    </span>
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        🕒
                                    </div>

                                    <span class="texto-gris-cursiva">
                                        Sin asignar
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td data-label="Camarero asignado" class="celda-flex">
                                <?php if ($p['camarero_nombre']): ?>
                                    <?php
                                    $nombreCompleto = trim($p['camarero_nombre'] . ' ' . $p['camarero_apellidos']);

                                    $avatarEmergencia = 'https://ui-avatars.com/api/?name='
                                        . urlencode($nombreCompletoCamarero)
                                        . '&background=random&color=fff&rounded=true&size=100';

                                    if (!empty($p['camarero_avatar_valor'])) {
                                        $avatarImg = (
                                            strpos($p['camarero_avatar_valor'], '/') === 0
                                            || strpos($p['camarero_avatar_valor'], 'http') === 0
                                        )
                                            ? $p['camarero_avatar_valor']
                                            : RUTA_APP . '/' . $p['camarero_avatar_valor'];
                                    } else {
                                        $avatarImgCamarero = $avatarEmergenciaCamarero;
                                    }
                                    ?>

                                    <img src="<?= escaparHtml($avatarImgCamarero) ?>"
                                         alt="Avatar camarero"
                                         onerror="this.onerror=null; this.src='<?= escaparHtml($avatarEmergenciaCamarero) ?>';"
                                         class="avatar-empleado">

                                    <span class="texto-destacado">
                                        <?= escaparHtml($nombreCompletoCamarero) ?>
                                    </span>
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        🕒
                                    </div>

                                    <span class="texto-gris-cursiva">
                                        Sin asignar
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <td data-label="Productos">
                                <ul>
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
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>