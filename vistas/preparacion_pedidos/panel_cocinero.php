<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../entities/pedido.php';

$user = require_role('cocinero'); 

// 1. PROCESAR ACCIONES DEL COCINERO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tomar_pedido'])) {
        // Pasa de "En preparación" a "Cocinando" y asigna al cocinero actual
        Pedido::asignarCocineroYEstado($_POST['pedido_id'], $user['id'], 'cocinando');
    } elseif (isset($_POST['marcar_plato'])) {
        // Marca un producto específico dentro del pedido como 'preparado'
        Pedido::marcarProductoPreparado($_POST['pedido_id'], $_POST['producto_id']);
    } elseif (isset($_POST['finalizar_pedido'])) {
        // Pasa de "Cocinando" a "Listo cocina"
        Pedido::cambiarEstado($_POST['pedido_id'], 'listo_cocina');
    }
}

// 2. OBTENER DATOS
$pedidosEnCola = Pedido::getPedidosPorEstado('en_preparacion'); 
$misPedidos = Pedido::getPedidosCocinando($user['id']);

//layout_header('Panel Cocina');
?>
<main>
    <div class="panel">
        <h2>👨‍🍳 Panel de Cocina - <?= htmlspecialchars($user['nombre']) ?></h2>
    </div>

    <section class="panel">
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
                                <h4 style="margin: 0 0 10px 0;">Pedido #<?= $p['id'] ?> (<?= strtoupper($p['tipo']) ?>)</h4>
                                
                                <ul style="list-style: none; padding: 0; margin-bottom: 15px;">
                                    <?php 
                                    $productos = Pedido::getProductosPedido($p['id']);
                                    $todosPreparados = true; 
                                    $hayProductos = count($productos) > 0;

                                    foreach ($productos as $prod): 
                                        $esPreparado = ($prod['estado'] === 'preparado');
                                        if (!$esPreparado) { $todosPreparados = false; } 
                                    ?>
                                        <li style="display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px dashed #ccc;">
                                            <span><?= $prod['cantidad'] ?>x <?= htmlspecialchars($prod['nombre']) ?></span>
                                            
                                            <?php if ($esPreparado): ?>
                                                <span style="color: green; font-weight: bold;">✅ Preparado</span>
                                            <?php else: ?>
                                                <form method="post" style="margin: 0;">
                                                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                                    <input type="hidden" name="producto_id" value="<?= $prod['producto_id'] ?>">
                                                    <button class="btn small" name="marcar_plato">Marcar Listo</button>
                                                </form>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                
                                <form method="post" style="margin: 0;">
                                    <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                    <?php if ($todosPreparados && $hayProductos): ?>
                                        <button class="btn success" name="finalizar_pedido" style="width: 100%;">
                                            🛎️ Finalizar Pedido (Mandar a Listo Cocina)
                                        </button>
                                    <?php else: ?>
                                        <button class="btn" disabled style="width: 100%; background-color: #ccc; cursor: not-allowed;" title="Prepara todos los platos primero">
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
    </section>

    <section class="panel">
        <h3>📋 Pedidos en Cola (En preparación)</h3>
        <div class="table-wrap">
            <table>
                <tbody>
                <?php if (empty($pedidosEnCola)): ?>
                    <tr><td>No hay pedidos esperando en la cola.</td></tr>
                <?php else: ?>
                    <?php foreach ($pedidosEnCola as $p): ?>
                        <tr>
                            <td><strong>Pedido #<?= $p['id'] ?></strong></td>
                            <td>
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
    </section>
</main>
</body>
</html>