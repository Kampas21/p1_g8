<?php declare(strict_types=1); ?>
<section class="panel">
    <h3>Histórico de pedidos</h3>

    <?php if (!$pedidosDisponibles): ?>
        <div class="table-wrap">
            <table class="w-full">
                <thead>
                    <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th><th>BistroCoins</th><th></th></tr>
                </thead>
                <tbody>
                    <tr><td colspan="7" class="muted">Sin datos reales todavía.</td></tr>
                </tbody>
            </table>
        </div>
    <?php elseif (empty($pedidosHistorico)): ?>
        <p>No hay pedidos registrados todavía.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="w-full">
                <thead>
                    <tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Total</th><th>Estado</th><th>BistroCoins</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidosHistorico as $p): ?>
                        <tr>
                            <td><?= escaparHtml((string) $p['numero_pedido']) ?></td>
                            <td><?= escaparHtml((string) $p['fecha_hora']) ?></td>
                            <td><?= escaparHtml((string) $p['tipo']) ?></td>
                            <td><?= escaparHtml((string) $p['total']) ?> €</td>
                            <td><?= escaparHtml(ucwords(str_replace('_', ' ', (string) $p['estado']))) ?></td>
                            <td>+<?= (int)($p['bistrocoins_generados'] ?? 0) ?> / -<?= (int)($p['bistrocoins_gastados'] ?? 0) ?></td>
                            <td>
                                <a class="btn small" href="<?= RUTA_APP ?>/vistas/pedidos/estadoPedido.php?id=<?= (int) $p['id'] ?>">Ver detalle</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>