<?php

require_once __DIR__ . '/../../includes/productoService.php';

// Aquí SOLO lógica
$productos = ProductoService::getAllByCategoria($categoria_id);