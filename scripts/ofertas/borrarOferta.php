<?php

require_once __DIR__ . '/../../includes/ofertaService.php';
require_once __DIR__ . '/../../includes/OfertaProductoDAO.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        http_response_code(400);
        die('ID de la oferta no válido.');
    }

    $oferta_pedido = OfertaService::ofertaEnUso($id);
    if($oferta_pedido) {
        http_response_code(403);
        die('La oferta está en uso y no se puede borrar.');
    }

    OfertaProductoDAO::removeProductosDeOferta((int)$id);
    OfertaService::borrarOferta((int)$id);

    header("Location: ../../vistas/ofertas/listarOfertas.php");
    exit();
}

http_response_code(405);
die('Método no permitido');
