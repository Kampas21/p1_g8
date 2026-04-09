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
                    <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidosHistorico as $p): ?>
                        <tr>
                            <td><?= e((string) $p['numero_pedido']) ?></td>
                            <td><?= e((string) $p['fecha_hora']) ?></td>
                            <td><?= e((string) $p['tipo']) ?></td>
                            <td><?= e((string) $p['total']) ?></td>
                            <td><?= e((string) $p['estado']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>