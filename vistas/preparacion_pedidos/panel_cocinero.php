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

<header class="panel">
    <h2>👨‍🍳 Panel de Cocina - <?= htmlspecialchars($user['nombre']) ?></h2>
</header>

<section class="panel panel-cocinando">
    <h3>🔥 Mis Pedidos (Cocinando)</h3>
    <div class="table-wrap">
        <table class="tabla-panel">
            <tbody>
            <?php if (empty($misPedidos)): ?>
                <tr>
                    <td class="tabla-panel-vacia">No estás cocinando ningún pedido ahora mismo.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($misPedidos as $p): ?>
                    <tr class="fila-pedido-cocinando">
                        <td class="celda-pedido-cocinando">
                            <h4 class="titulo-pedido-cocinando">Pedido #<?= $p['id'] ?> (<?= strtoupper($p['tipo']) ?>)</h4>
                            
                            <ul class="lista-platos">
                                <?php 
                                $productos = Pedido::getProductosPedido($p['id']);
                                $todosPreparados = true; 
                                $hayProductos = count($productos) > 0;

                                foreach ($productos as $prod): 
                                    $esPreparado = ($prod['estado'] === 'preparado');
                                    if (!$esPreparado) { $todosPreparados = false; } 
                                ?>
                                    <li class="item-plato">
                                        <span><strong><?= $prod['cantidad'] ?>x</strong> <?= htmlspecialchars($prod['nombre']) ?></span>
                                        
                                        <?php if ($esPreparado): ?>
                                            <span class="estado-plato-listo">✅ Preparado</span>
                                        <?php else: ?>
                                            <form method="post" class="form-inline">
                                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="producto_id" value="<?= $prod['producto_id'] ?>">
                                                <button class="btn small primary" name="marcar_plato">Marcar Listo</button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <form method="post" class="form-inline">
                                <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                                <?php if ($todosPreparados && $hayProductos): ?>
                                    <button class="btn success btn-bloque" name="finalizar_pedido">
                                        🛎️ Finalizar Pedido (Mandar a Listo Cocina)
                                    </button>
                                <?php else: ?>
                                    <button class="btn-bloque-disabled" disabled title="Prepara todos los platos primero">
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

<section class="panel panel-esperando">
    <h3>📋 Pedidos en Cola (Esperando)</h3>
    <div class="table-wrap">
        <table class="tabla-panel">
            <tbody>
            <?php if (empty($pedidosEnCola)): ?>
                <tr><td class="tabla-panel-vacia">No hay pedidos esperando en la cola.</td></tr>
            <?php else: ?>
                <?php foreach ($pedidosEnCola as $p): ?>
                    <tr class="tabla-panel-fila">
                        <td><strong>Pedido #<?= $p['id'] ?></strong></td>
                        <td style="text-align: right;"> <!-- Un solo style inofensivo permitido por el grid -->
                            <form method="post" class="form-inline">
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

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>