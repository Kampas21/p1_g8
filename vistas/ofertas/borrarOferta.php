<?php
require_once __DIR__ . '/../../includes/ofertaDAO.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    echo '<p>ID de la oferta no válido.</p>';
    exit();
}

$oferta = OfertaService::getById((int)$id);
$oferta_pedido = OfertaService::ofertaEnUso((int)$id);

if (!$oferta) {
    echo '<p>La oferta no existe.</p>';
    exit();
}

$tituloPagina = 'Borrar oferta';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1>Borrar <?= htmlspecialchars($oferta->getNombre()) ?></h1>

<?php if($oferta_pedido): ?>
    <p>La oferta está en uso y no se puede borrar. Aparece en los siguientes pedidos:</p>
    <ul>
    <?php foreach ($oferta_pedido as $op): ?>
        <li>Pedido #<?= (int)$op['pedido_id'] ?></li>
    <?php endforeach; ?>
    </ul>
    <p>
        <a class="btn-volver" href="listarOfertas.php">Volver</a>
    </p>
<?php else: ?>
    <p>¿Seguro que quieres borrar esta oferta <strong><?= htmlspecialchars($oferta->getNombre()) ?></strong>?</p>

    <form method="POST" action="../../scripts/ofertas/borrarOferta.php">
        <input type="hidden" name="id" value="<?= (int)$oferta->getId() ?>">
        <p><button type="submit" class="btn-aceptar">Sí, borrar</button></p>
    </form>

    <p>
        <a class="btn-volver" href="listarOfertas.php">Cancelar</a>
    </p>
<?php endif; ?>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
