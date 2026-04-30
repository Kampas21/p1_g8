<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

require_once __DIR__ . '/../../entities/pedido.php';

require_once __DIR__ . '/../../includes/Formulario/FormularioActualizarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioEliminarLineaPedido.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCancelarPedido.php';

require_once __DIR__ . '/../../includes/pedidoService.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';

$user = require_login();

if (!PedidoService::carritoTieneTipo()) {
    flash_set('info', 'No tienes carrito. Inicia un pedido para añadir productos.');
    redirect('elegirTipo.php');
}

$lineas = PedidoService::getCarritoItems();
$tipoPedido = PedidoService::getTipoCarrito();
$ofertas_aplicadas = PedidoService::getCarritoOfertas();

$saldoBistrocoins = method_exists($user, 'getBistrocoins') ? (int) $user->getBistrocoins() : 0;

// Totales preparados para soportar también futuras líneas de recompensa
$total = 0.0;
$bistrocoins_reservados = 0;

foreach ($lineas as $clave => $item) {
    $cantidad = (int) ($item['cantidad'] ?? 1);
    $precio = (float) ($item['precio_unitario'] ?? 0);
    $esRecompensa = !empty($item['es_recompensa']);
    $bistrocoinsUnitarios = (int) ($item['bistrocoins_unitarios'] ?? 0);

    if ($esRecompensa) {
        $bistrocoins_reservados += $cantidad * $bistrocoinsUnitarios;
    } else {
        $total += $precio * $cantidad;
    }
}

$total = round($total, 2);
$descuento_total = PedidoService::calcularDescuentoCarrito();
$total_final = max(0, round($total - $descuento_total, 2));

$formsActualizarHtml = [];
$formsEliminarHtml = [];

// Mantenemos tu lógica actual y además evitamos crear formularios para líneas recompensa
foreach ($lineas as $clave => $item) {
    $prod_id = isset($item['producto_id']) ? (int) $item['producto_id'] : (int) $clave;
    $esRecompensa = !empty($item['es_recompensa']);

    if ($esRecompensa) {
        continue;
    }

    $formUpdate = new \es\ucm\fdi\aw\Formulario\FormularioActualizarLineaPedido(
        $prod_id,
        (int) ($item['cantidad'] ?? 1)
    );
    $formsActualizarHtml[$prod_id] = $formUpdate->gestiona();

    $formRemove = new \es\ucm\fdi\aw\Formulario\FormularioEliminarLineaPedido(
        $prod_id
    );
    $formsEliminarHtml[$prod_id] = $formRemove->gestiona();
}

$formCancelar = new \es\ucm\fdi\aw\Formulario\FormularioCancelarPedido();
$htmlFormCancelar = $formCancelar->gestiona();

$tituloPagina = 'Mi carrito | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<?php if (!empty($_SESSION['errores_ofertas'])): ?>
    <script>
        alert("⚠️ Algunas ofertas no se han podido aplicar:\n\n<?= implode('\n', array_map('addslashes', $_SESSION['errores_ofertas'])) ?>");
    </script>
    <?php unset($_SESSION['errores_ofertas']); ?>
<?php endif; ?>

<main>

    <?php foreach (flash_get_all() as $f): ?>
        <div class="mensaje-<?= e($f['type']) ?>">
            <?= e($f['message']) ?>
        </div>
    <?php endforeach; ?>

    <div class="panel">

        <h2>
            Mi carrito
            <span class="text-muted-italic">
                — pedido <?= e($tipoPedido === 'local' ? '🍽️ en local' : '🥡 para llevar') ?>
            </span>
        </h2>

        <div class="panel" style="margin-bottom: 20px;">
            <p><strong>Saldo actual:</strong> <?= e((string) $saldoBistrocoins) ?> BistroCoins</p>

            <?php if ($bistrocoins_reservados > 0): ?>
                <p><strong>BistroCoins reservados en este pedido:</strong> <?= e((string) $bistrocoins_reservados) ?></p>
            <?php endif; ?>
        </div>

        <?php if (empty($lineas)): ?>
            <p>El carrito está vacío.</p>

            <div class="actions-inline">
                <a href="catalogo.php" class="btn">← Seguir comprando</a>
                <a href="<?= RUTA_APP ?>/vistas/recompensas/recompensaCliente.php" class="btn-nuevo">Recompensas</a>
                <?= $htmlFormCancelar ?>
            </div>

        <?php else: ?>

            <?php if (!empty($ofertas_aplicadas)): ?>
                <div class="panel">
                    <h3>🟢 Ofertas aplicadas</h3>

                    <ul>
                        <?php foreach ($ofertas_aplicadas as $o): ?>
                            <li>
                                <strong><?= e($o['nombre'] ?? '') ?></strong>
                                — <?= (int) ($o['veces_aplicada'] ?? 0) ?>x
                                — -<?= round((float) ($o['descuento_total'] ?? 0), 2) ?> €

                                <div style="margin-left:15px; font-size: 0.9em; color:#666;">
                                    <?php
                                    $productos_oferta = ProductoDAO::getProductosDeOferta(
                                        $o['oferta_id']
                                    );

                                    foreach ($productos_oferta as $po):
                                    ?>
                                        • <?= e($po->getNombre()) ?> (x<?= $po->cantidad ?>)<br>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($lineas as $clave => $item):
                            $prod_id = isset($item['producto_id']) ? (int) $item['producto_id'] : (int) $clave;
                            $producto = ProductoDAO::getById($prod_id);
                            if (!$producto) {
                                continue;
                            }

                            $cantidad = (int) ($item['cantidad'] ?? 1);
                            $precio = (float) ($item['precio_unitario'] ?? 0);
                            $esRecompensa = !empty($item['es_recompensa']);
                            $bistrocoinsUnitarios = (int) ($item['bistrocoins_unitarios'] ?? 0);
                        ?>
                            <tr>
                                <td>
                                    <img src="<?= e(RUTA_APP . '/' . $producto->getImagen()) ?>"
                                         class="img-thumbnail"
                                         alt="<?= e($producto->getNombre()) ?>">
                                </td>

                                <td>
                                    <a href="<?= RUTA_APP ?>/vistas/productos/detalle_producto.php?id=<?= $producto->getId() ?>" class="click">
                                        <?= e($producto->getNombre()) ?>
                                    </a>
                                    <?php if ($esRecompensa): ?>
                                        <br><small><strong>(Recompensa)</strong></small>
                                    <?php endif; ?>
                                </td>

                                <td class="col-precio">
                                    <?php if ($esRecompensa): ?>
                                        <?= e((string) $bistrocoinsUnitarios) ?> BC
                                    <?php else: ?>
                                        <?= e((string) $precio) ?> €
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($esRecompensa): ?>
                                        <?= e((string) $cantidad) ?>
                                    <?php else: ?>
                                        <?= $formsActualizarHtml[$prod_id] ?? e((string) $cantidad) ?>
                                    <?php endif; ?>
                                </td>

                                <td class="col-precio">
                                    <?php if ($esRecompensa): ?>
                                        <?= e((string) ($bistrocoinsUnitarios * $cantidad)) ?> BC
                                    <?php else: ?>
                                        <?= round($precio * $cantidad, 2) ?> €
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!$esRecompensa): ?>
                                        <?= $formsEliminarHtml[$prod_id] ?? '' ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="4"><strong>Total productos de pago:</strong></td>
                            <td class="col-precio"><strong><?= $total ?> €</strong></td>
                            <td></td>
                        </tr>

                        <?php if ($descuento_total > 0): ?>
                            <tr>
                                <td colspan="4"><strong>Descuento:</strong></td>
                                <td class="col-precio"><strong>-<?= round($descuento_total, 2) ?> €</strong></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <td colspan="4"><strong>Total a pagar:</strong></td>
                            <td class="col-precio"><strong><?= round($total_final, 2) ?> €</strong></td>
                            <td></td>
                        </tr>

                        <?php if ($bistrocoins_reservados > 0): ?>
                            <tr>
                                <td colspan="4"><strong>BistroCoins reservados:</strong></td>
                                <td class="col-precio"><strong><?= e((string) $bistrocoins_reservados) ?> BC</strong></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>

            <div class="actions-inline">
                <a href="catalogo.php" class="btn">← Seguir comprando</a>
                <a href="../ofertas/ofertaCliente.php?modo=edicion" class="btn-nuevo">Ofertas</a>
                <a href="<?= RUTA_APP ?>/vistas/recompensas/recompensaCliente.php" class="btn-nuevo">Recompensas</a>
                <a href="pago.php" class="btn primary">Confirmar</a>
                <?= $htmlFormCancelar ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>