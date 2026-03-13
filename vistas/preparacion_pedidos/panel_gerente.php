<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../entities/pedido.php';

$user = require_role('gerente'); 
$pedidos = Pedido::getPedidosPendientesGerente();

layout_header('Panel Gerencia');
?>
<main>
    <div class="panel">
        <h2>👔 Visión Global de Gerencia</h2>
        <p>Usuario: <?= htmlspecialchars($user['nombre']) ?> (Rol: <?= htmlspecialchars($user['rol']) ?>)</p>
    </div>

    <section class="panel">
        <h3>📊 Todos los Pedidos Pendientes</h3>
        <div class="table-wrap">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f1f1f1; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">ID</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Estado</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Cocinero Asignado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr><td colspan="3" style="text-align: center; padding: 20px;">No hay pedidos pendientes en este momento.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidos as $p): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px;"><strong>#<?= (int)$p['id'] ?></strong></td>
                            <td style="padding: 10px;">
                                <?php
                                    $colores = [
                                        'recibido' => '#ffc107', 'en_preparacion' => '#17a2b8',
                                        'cocinando' => '#fd7e14', 'listo_cocina' => '#28a745', 'terminado' => '#6c757d'
                                    ];
                                    $color = $colores[$p['estado']] ?? '#333';
                                ?>
                                <span style="background: <?= $color ?>; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.85em; font-weight: bold;">
                                    <?= strtoupper(str_replace('_', ' ', $p['estado'])) ?>
                                </span>
                            </td>
                            <td style="padding: 10px; display: flex; align-items: center; gap: 12px;">
                                <?php if ($p['cocinero_nombre']): ?>
                                    <?php 
                                        $nombreCompleto = trim($p['cocinero_nombre'] . ' ' . $p['cocinero_apellidos']);
                                        $avatarImg = '';
                                        
                                        // Generamos SIEMPRE el avatar de emergencia por si acaso
                                        $avatarEmergencia = 'https://ui-avatars.com/api/?name=' . urlencode($nombreCompleto) . '&background=random&color=fff&rounded=true&size=100';

                                        if (!empty($p['avatar_valor'])) {
                                            $ruta = $p['avatar_valor'];
                                            if (strpos($ruta, '/') === 0 || strpos($ruta, 'http') === 0) {
                                                $avatarImg = $ruta;
                                            } else {
                                                $avatarImg = '/p1_g8/' . $ruta;
                                            }
                                        } else {
                                            $avatarImg = $avatarEmergencia;
                                        }
                                    ?>
                                    <img src="<?= htmlspecialchars($avatarImg) ?>" 
                                         alt="Avatar" 
                                         onerror="this.onerror=null; this.src='<?= $avatarEmergencia ?>';" 
                                         style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <span style="font-weight: 500;"><?= htmlspecialchars($nombreCompleto) ?></span>
                                <?php else: ?>
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background-color: #eee; display: flex; justify-content: center; align-items: center; border: 1px dashed #ccc;">
                                        🕒
                                    </div>
                                    <span style="color: gray; font-style: italic;">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>