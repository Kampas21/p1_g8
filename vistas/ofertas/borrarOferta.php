<?php

require_once __DIR__ . '/../../includes/ofertaService.php';
require_once __DIR__ . '/../../includes/ofertaProductoService.php';

$id = $_GET['id'] ?? null;


if (!$id || !is_numeric($id)) {
    echo '<p>ID de la oferta no válido.</p>';
    exit();
}


$oferta = OfertaService::getById((int)$id);
$oferta_pedido = OfertaService::ofertaEnUso((int)$id);

if (!$oferta) {
    echo '<p>La ofeta no existe.</p>';
    exit();
}

if($oferta_pedido){
    echo '<p>La oferta está en uso en los siguientes pedidos:</p>';
    echo '<ul>';

    foreach ($oferta_pedido as $op) {
        echo '<li>Pedido #' . $op['pedido_id'] . '</li>';
    }

    echo '</ul>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    OfertaProductoService::removeProductosDeOferta((int)$id);
    OfertaService::borrarOferta((int)$id);

    header("Location: listarOfertas.php");
    exit();
}

$tituloPagina = 'Activar oferta';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1>Borrar <?= htmlspecialchars($oferta->getNombre()) ?></h1>

<p>¿Seguro que quieres volver a borrar esta oferta <strong><?= htmlspecialchars($oferta->getNombre()) ?></strong>?</p>

<form method="POST">
    <p><button type="submit" class="btn-aceptar">Sí, activar</button></p>
</form>

<p>
    <a class="btn-volver" href="listarOfertas.php">
        Cancelar
    </a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';