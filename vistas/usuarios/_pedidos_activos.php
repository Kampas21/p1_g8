<?php declare(strict_types=1); ?>
<section class="panel progress-card">
    <h3>Pedidos activos</h3>
    <p class="muted">Estados relevantes: En preparación / Cocinando / Listo cocina / Terminado.</p>

    <?php if (!$pedidosDisponibles): ?>
        <div class="pedido-linea">
            <strong>Ejemplo visual</strong>
            <div class="progress-steps">
                <div class="progress-step done"><div class="dot"></div>En preparación</div>
                <div class="progress-step done"><div class="dot"></div>Cocinando</div>
                <div class="progress-step active"><div class="dot"></div>Listo cocina</div>
                <div class="progress-step"><div class="dot"></div>Terminado</div>
            </div>
        </div>
    <?php elseif (empty($pedidosActivos)): ?>
        <p>No tienes pedidos activos en este momento.</p>
    <?php else: ?>
        <?php foreach ($pedidosActivos as $p): ?>
            <!-- Script de apoyo iterativo renderizado limpio sin SQL -->
            <div class="pedido-linea">
                <strong>Pedido #<?= e((string) $p['numero_pedido']) ?></strong>
                <div class="muted">Estado actual: <?= e((string) $p['estado']) ?></div>
                <div class="muted">Importe: <?= e((string) $p['total']) ?> €</div>
                <?php if (!empty($p['lineas'])): ?>
                    <ul>
                        <?php foreach ($p['lineas'] as $linea): ?>
                            <li>
                                <?= e((string)$linea['nombre']) ?>: <?= (int)$linea['cantidad'] ?>
                                <?= ((int)$linea['es_recompensa'] === 1) ? '(Recompensa)' : '' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>