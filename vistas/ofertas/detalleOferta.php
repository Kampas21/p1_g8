<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/auth.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Necesitas ser gerente para acceder a esta oferta.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
<?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

require_once __DIR__ . '/../../entities/oferta.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID de oferta no proporcionado");
}

$oferta = OfertaDAO::getById($id);

if (!$oferta) {
    die("Oferta no encontrada");
}

$tituloPagina = 'Detalle de oferta';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Detalle de la oferta</h1>

<div class="panel">

    <p><strong>Nombre:</strong> <?= htmlspecialchars($oferta->getNombre()) ?></p>

    <p><strong>Descripción:</strong><br>
        <?= nl2br(htmlspecialchars($oferta->getDescripcion())) ?>
    </p>

    <p><strong>Fecha inicio:</strong>
        <?= date('d/m/Y H:i', strtotime($oferta->getFechaInicio())) ?>
    </p>

    <p><strong>Fecha fin:</strong>
        <?= date('d/m/Y H:i', strtotime($oferta->getFechaFin())) ?>
    </p>

    <p><strong>Estado:</strong>
        <?= (!$oferta->estaActiva())
            ? '<span class="text-danger">Caducada</span>'
            : '<span class="text-success">Activa</span>' ?>
    </p>

    <hr>

    <h3>Productos incluidos</h3>

    <?php
    $precio_total = 0;
    $productos = ProductoService::getProductosDeOferta($oferta->getId());
    ?>

    <?php if (empty($productos)): ?>
        <p>No hay productos en esta oferta.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($productos as $p): 
                $precio = $p->getPrecioFinal();
                $precio_cant = $precio * $p->cantidad;
                $precio_total += $precio_cant;
            ?>
                <li>
                    <?= htmlspecialchars($p->getNombre()) ?>
                    (<?= $p->cantidad ?> uds)
                    → <?= round($precio_cant, 2) ?> €
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr>

    <h3>Resumen económico</h3>

    <p><strong>Precio total:</strong> <?= round($precio_total, 2) ?> €</p>

    <p><strong>Descuento:</strong> <?= $oferta->getDescuento() ?> %</p>

    <?php
    $precio_final = $oferta->aplicarDescuento($precio_total);
    ?>

    <p><strong>Precio final:</strong> <?= round($precio_final, 2) ?> €</p>

</div>

<p>
    <a class="btn-volver" href="listarOfertas.php">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';