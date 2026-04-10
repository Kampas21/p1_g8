<?php

require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

$user = require_role('gerente'); 
$pedidos = PedidoService::getPedidosPendientesGerente();

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Panel de Gerencia | Bistro FDI';
ob_start();
?>
  
<div class="panel">
    <h2>👔 Visión Global de Gerencia</h2>
    <p>Usuario: <?= htmlspecialchars($user->getNombre()) ?> (Rol: <?= htmlspecialchars($user->getRol()) ?>)</p>
</div>

<div class="panel">
    <h3>📊 Todos los Pedidos Pendientes</h3>
    <div class="table-wrap">
        <table class="tabla-panel">
            <thead>
                <tr class="tabla-panel-cabecera">
                    <th>ID</th>
                    <th>Estado</th>
                    <th>Cocinero Asignado</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($pedidos)): ?>
                <tr>
                    <td colspan="3" class="tabla-panel-vacia">No hay pedidos pendientes en este momento.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pedidos as $p): ?>
                    <tr class="tabla-panel-fila">
                        <td><strong>#<?= (int)$p['id'] ?></strong></td>
                        <td>
                            <span class="badge-estado estado-<?= e($p['estado']) ?>">
                                <?= strtoupper(str_replace('_', ' ', $p['estado'])) ?>
                            </span>
                        </td>
                        <td class="celda-flex">
                            <?php if ($p['cocinero_nombre']): ?>
                                <?php 
                                    $nombreCompleto = trim($p['cocinero_nombre'] . ' ' . $p['cocinero_apellidos']);
                                    $avatarEmergencia = 'https://ui-avatars.com/api/?name=' . urlencode($nombreCompleto) . '&background=random&color=fff&rounded=true&size=100';

                                    $avatarImg = (!empty($p['avatar_valor'])) 
                                        ? (strpos($p['avatar_valor'], '/') === 0 || strpos($p['avatar_valor'], 'http') === 0 ? $p['avatar_valor'] : RUTA_APP.'/' . $p['avatar_valor']) 
                                        : $avatarEmergencia;
                                ?>
                                <img src="<?= e($avatarImg) ?>" 
                                     alt="Avatar" 
                                     onerror="this.onerror=null; this.src='<?= e($avatarEmergencia) ?>';" 
                                     class="avatar-empleado">
                                <span class="texto-destacado"><?= e($nombreCompleto) ?></span>
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    🕒
                                </div>
                                <span class="texto-gris-cursiva">Sin asignar</span>
                            <?php endif; ?>
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