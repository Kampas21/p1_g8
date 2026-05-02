<?php

session_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ofertaService.php';

$user = require_login();

/**
 * 🔹 Toggle de ofertas en sesión
 */
$oferta_id = $_POST['oferta'] ?? null;

if (!isset($_SESSION['ofertas_seleccionadas'])) {
    $_SESSION['ofertas_seleccionadas'] = [];
}

if ($oferta_id !== null) {

    if (in_array($oferta_id, $_SESSION['ofertas_seleccionadas'])) {
        $_SESSION['ofertas_seleccionadas'] = array_values(
            array_diff($_SESSION['ofertas_seleccionadas'], [$oferta_id])
        );
    } else {
        $_SESSION['ofertas_seleccionadas'][] = $oferta_id;
    }
}

/**
 * 🔥 Aplicar TODAS las ofertas actuales
 */
$errores = OfertaService::aplicarOfertas($_SESSION['ofertas_seleccionadas']);

$_SESSION['errores_ofertas'] = $errores;

header("Location: ../../vistas/pedidos/carrito.php");
exit;