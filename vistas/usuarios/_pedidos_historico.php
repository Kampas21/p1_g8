<?php declare(strict_types=1); ?>
<section class="panel">
    <h3>Histórico de pedidos</h3>

    <?php if (!$pedidosDisponibles): ?>
        <div class="table-wrap">
            <table class="w-full">
                <thead>
                    <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="muted">Sin datos reales todavía.</td></tr>
                </tbody>
            </table>
        </div>
    <?php elseif (empty($pedidosHistorico)): ?>
        <p>No hay pedidos registrados todavía.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="w-full">
                <thead>
                    <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th><th>BistroCoins</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidosHistorico as $p): ?>
                        <tr>
                            <td><?= e((string) $p['numero_pedido']) ?></td>
                            <td><?= e((string) $p['fecha_hora']) ?></td>
                            <td><?= e((string) $p['tipo']) ?></td>
                            <td><?= e((string) $p['total']) ?> €</td>
                            <td><?= e((string) $p['estado']) ?></td>
                            <td>+<?= (int)($p['bistrocoins_generados'] ?? 0) ?> / -<?= (int)($p['bistrocoins_gastados'] ?? 0) ?></td>
                        </tr>
                        <?php if (!empty($p['lineas'])): ?>
                            <tr>
                                <td colspan="6">
                                    <strong>Detalle:</strong>
                                    <ul>
                                        <?php foreach ($p['lineas'] as $linea): ?>
                                            <li>
                                                <?= e((string)$linea['nombre']) ?>: <?= (int)$linea['cantidad'] ?>
                                                <?= ((int)$linea['es_recompensa'] === 1) ? '(Recompensa)' : '' ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
