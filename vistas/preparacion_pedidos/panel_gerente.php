<?php

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../entities/Pedido.php';
require_once __DIR__ . '/../../includes/PedidoService.php';

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
                        <?php require __DIR__ . '/_fila_pedido_gerente.php'; ?>
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