<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../entities/pedido.php';

$user = require_role('cocinero'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tomar_pedido'])) {
        Pedido::asignarCocineroYEstado($_POST['pedido_id'], $user['id'], 'cocinando');
    } elseif (isset($_POST['marcar_plato'])) {
        Pedido::marcarProductoPreparado($_POST['pedido_id'], $_POST['producto_id']);
    } elseif (isset($_POST['finalizar_pedido'])) {
        Pedido::cambiarEstado($_POST['pedido_id'], 'listo_cocina');
    }
    header("Location: panel_cocinero.php");
    exit;
}

$pedidosEnCola = Pedido::getPedidosPorEstado('en_preparacion'); 
$misPedidos = Pedido::getPedidosCocinando($user['id']);

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = 'Panel de Cocina | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2>👨‍🍳 Panel de Cocina - <?= htmlspecialchars($user['nombre']) ?></h2>
</div>

<div class="panel" style="border-top: 4px solid #fd7e14;">
    <h3>🔥 Mis Pedidos (Cocinando)</h3>
    <div class="table-wrap">
        <table style="width: 100%; border-collapse: collapse;">
            <tbody>
            <?php if (empty($misPedidos)): ?>
                <tr><td style="padding: 15px; text-align: center;">No estás cocinando ningún pedido ahora mismo.</td></tr>
            <?php else: ?>
                <?php foreach ($misPedidos as $p): ?>
                    <tr style="background-color: #f9f9f9; border-bottom: 2px solid #ddd;">
                        <td style="padding: 15px;">
                            <h4 style="margin: 0 0 10px 0; color: #d35400;">Pedido #<?= $p['id'] ?> (<?= strtoupper($p['tipo']) ?>)</h4>
                            
                            <ul style="list-style: none; padding: 0; margin-bottom: 15px;">
                                <?php 
                                $productos = Pedido::getProductosPedido($p['id']);
                                $todosPreparados = true; 
                                $hayProductos = count($productos) > 0;

                                foreach ($productos as $prod): 
                                    $esPreparado = ($prod['estado'] === 'preparado');
                                    if (!$esPreparado) { $todosPreparados = false; } 
                                ?>
                                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed #ccc;">
                                        <span><strong><?= $prod['cantidad'] ?>x</strong> <?= htmlspecialchars($prod['nombre']) ?></span>
                                        
                                        <?php if ($esPreparado): ?>
                                            <span style="color: green; font-weight: bold;">✅ Preparado</span>
                                        <?php else: ?>
                                            <form method="post" style="margin: 0;">
                                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="producto_id" value="<?= $prod['producto_id'] ?>">
                                                <button class="btn small primary" name="marcar_plato">Marcar Listo</button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                <?php if ($todosPreparados && $hayProductos): ?>
                                    <button class="btn success" name="finalizar_pedido" style="width: 100%; font-size: 1.1em; padding: 10px;">
                                        🛎️ Finalizar Pedido (Mandar a Listo Cocina)
                                    </button>
                                <?php else: ?>
                                    <button class="btn" disabled style="width: 100%; background-color: #ccc; color: #666; cursor: not-allowed; padding: 10px;" title="Prepara todos los platos primero">
                                        ⏳ Faltan platos por preparar...
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="panel" style="margin-top: 20px; border-top: 4px solid #17a2b8;">
    <h3>📋 Pedidos en Cola (Esperando)</h3>
    <div class="table-wrap">
        <table>
            <tbody>
            <?php if (empty($pedidosEnCola)): ?>
                <tr><td>No hay pedidos esperando en la cola.</td></tr>
            <?php else: ?>
                <?php foreach ($pedidosEnCola as $p): ?>
                    <tr>
                        <td><strong>Pedido #<?= $p['id'] ?></strong></td>
                        <td style="text-align: right;">
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                <button class="btn primary" name="tomar_pedido">🍳 Tomar Pedido</button>
                            </form>
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