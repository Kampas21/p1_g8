<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';

$user = current_user();

if (!$user) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();

?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Debes iniciar sesión para ver las ofertas.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
<?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

//require_once __DIR__ . '/../../entities/oferta.php';
//require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/ofertaService.php';


$pedido_id = $_POST['pedido_id'] ?? null;
$modoSeleccion = $pedido_id !== null;


$ofertas = OfertaService::getAllActivas();

$tituloPagina = 'Ofertas disponibles';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<h1>Ofertas disponibles</h1>

<?php if ($modoSeleccion): ?>
<form method="POST" action="aplicarOfertas.php">
    <input type="hidden" name="pedido_id" value="<?= $pedido_id ?>">
<?php endif; ?>

    <div class="panel table-wrap">
        <table>
            <tr>
                <?php if ($modoSeleccion): ?>
                    <th>Seleccionar</th>
                <?php endif; ?>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Productos</th>
                <th>Precio total</th>
                <th>Descuento</th>
                <th>Precio final</th>
            </tr>

            <?php foreach ($ofertas as $oferta): ?>
                <tr>

                    <!-- CHECKBOX -->
                    <?php if ($modoSeleccion): ?>
                        <td>
                            <input type="checkbox" name="ofertas[]" value="<?= $oferta->getId() ?>">
                        </td>
                    <?php endif; ?>

                    <td><?= htmlspecialchars($oferta->getNombre()) ?></td>
                    <td><?= htmlspecialchars($oferta->getDescripcion()) ?></td>

                    <?php
                    $precio_total = 0;
                    $productos = ProductoService::getProductosDeOferta($oferta->getId());

                    $lista = array_map(function ($p) use (&$precio_total) {
                        $precio = $p->getPrecioFinal();
                        $precio_cant = $precio * $p->cantidad;
                        $precio_total += $precio_cant;
                        return $p->getNombre() . ' (' . $p->cantidad . ') ' . round($precio_cant, 2) . '€';
                    }, $productos);
                    ?>

                    <td>
                        <?php
                        foreach (array_chunk($lista, 2) as $grupo) {
                            echo htmlspecialchars(implode(', ', $grupo)) . "<br>";
                        }
                        ?>
                    </td>

                    <td><?= round($precio_total, 2) . '€' ?></td>
                    <td><?= $oferta->getDescuento() . '%' ?></td>

                    <?php
                    $precio_final = $oferta->aplicarDescuento($precio_total);
                    ?>

                    <td><?= $precio_final . '€' ?></td>

                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <br>


    <?php if ($modoSeleccion): ?>
        <button type="submit" class="btn-nuevo">Aplicar ofertas</button>
</form>
<?php endif; ?>

</form>

<p>
    <a class="btn-volver" href="../../index.php">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
