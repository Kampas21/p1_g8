<?php

require_once __DIR__ . '/../../entities/ofertaProducto.php';
require_once __DIR__ . '/../../entities/oferta.php';

$id = $_GET['id'] ?? null;


if (!$id || !is_numeric($id)) {
    echo '<p>ID de la oferta no válido.</p>';
    exit();
}


$oferta = Oferta::getOfertaById((int)$id);
$oferta_pedido = Oferta::ofertaEnUso((int)$id);

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
    
    OfertaProducto::removeProductosDeOferta((int)$id);
    Oferta::borrarOferta((int)$id);

    header("Location: listarOfertas.php");
    exit();
}

$tituloPagina = 'Activar oferta';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1>Borrar <?= htmlspecialchars($oferta['nombre']) ?></h1>

<p>¿Seguro que quieres volver a borrar esta oferta <strong><?= htmlspecialchars($oferta['nombre']) ?></strong>?</p>

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