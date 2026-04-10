<?php
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$categorias = CategoriaService::getAll();

$catalogo = [];

foreach ($categorias as $cat) {
    $productos = ProductoService::getAllByCategoria($cat->getId());

    // solo ofertados y disponibles
    $productosFiltrados = array_filter($productos, function($p) {
        return $p->isOfertado() && $p->isDisponible();
    });

    $catalogo[] = [
        'categoria' => $cat,
        'productos' => $productosFiltrados
    ];
}