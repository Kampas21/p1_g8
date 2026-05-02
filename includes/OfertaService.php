<?php

require_once __DIR__ . '/PedidoService.php';
require_once __DIR__ . '/OfertaDAO.php';
require_once __DIR__ . '/ProductoDAO.php';

class OfertaService
{
    // public static function aplicarOfertas(array $ofertas_ids): array
    // {
    //     $errores = [];

    //     if (!PedidoService::carritoTieneProductos()) {
    //         return ["El carrito está vacío"];
    //     }

    //     PedidoService::limpiarOfertasCarrito();

    //     $pedido_productos = [];

    //     foreach (PedidoService::getCarritoItems() as $item) {
    //         if (!empty($item['es_recompensa'])) continue;
    //         $pid = $item['producto_id'];
    //         $pedido_productos[$pid] = ($pedido_productos[$pid] ?? 0) + (int)($item['cantidad'] ?? 0);
    //     }

    //     $ofertas_validas = [];

    //     foreach ($ofertas_ids as $oferta_id) {

    //         $oferta = OfertaDAO::getById($oferta_id);
    //         if (!$oferta) {
    //             $errores[] = "La oferta $oferta_id no existe";
    //             continue;
    //         }

    //         $productos_oferta = ProductoDAO::getProductosDeOferta($oferta_id);

    //         $veces = PHP_INT_MAX;
    //         $faltantes = [];
    //         $reserva = [];
    //         $precio_pack = 0;


    //         foreach ($productos_oferta as $po) {

    //             $id = $po->getId();
    //             $req = $po->cantidad;
    //             $disponible = $pedido_productos[$id] ?? 0;

    //             // calcular cuántas veces puede aplicarse este producto
    //             $veces = min($veces, ($req > 0) ? intdiv($disponible, $req) : 0);

    //             // faltantes
    //             if ($disponible < $req) {
    //                 $faltantes[] = $po->getNombre() .
    //                     " (necesitas {$req}, tienes {$disponible})";
    //             }

    //             // precio del pack
    //             $precio_pack += $po->getPrecioFinal() * $req;
    //         }

    //         //  no aplicable
    //         if ($veces <= 0) {
    //             $errores[] = "Faltan productos en '{$oferta->getNombre()}': " . implode(', ', $faltantes);
    //             continue;
    //         }


    //         foreach ($productos_oferta as $po) {
    //             $id = $po->getId();
    //             $reserva[$id] = $po->cantidad * $veces;
    //         }

    //         // descuento
    //         $descuento_total = ($precio_pack * ($oferta->getDescuento() / 100)) * $veces;

    //         PedidoService::agregarOfertaAlCarrito(
    //             (int)$oferta_id,
    //             $oferta->getNombre(),
    //             $veces,
    //             $descuento_total
    //         );

    //         foreach ($reserva as $id => $cantidad) {
    //             $pedido_productos[$id] -= $cantidad;
    //         }

    //         $ofertas_validas[] = $oferta_id;
    //     }

    //     $_SESSION['ofertas_seleccionadas'] = $ofertas_validas;

    //     return $errores;
    // }

    public static function aplicarOfertas(array $ofertas_ids): array
    {
        $errores = [];

        if (!PedidoService::carritoTieneProductos()) {
            return ["El carrito está vacío"];
        }

        PedidoService::limpiarOfertasCarrito();

        $itemsCarrito = PedidoService::getCarritoItems();

        // mapa producto_id => cantidad
        $pedido_productos = [];
        foreach ($itemsCarrito as $item) {
            if (!empty($item['es_recompensa'])) continue;

            $pid = $item['producto_id'];
            $pedido_productos[$pid] = ($pedido_productos[$pid] ?? 0) + (int)$item['cantidad'];
        }

        $ofertas_validas = [];

        foreach ($ofertas_ids as $oferta_id) {

            $oferta = OfertaDAO::getById($oferta_id);
            if (!$oferta) {
                $errores[] = "La oferta $oferta_id no existe";
                continue;
            }

            $datos = self::calcularDatosOferta($oferta, $pedido_productos, $itemsCarrito);

            if (!$datos || isset($datos['error'])) {
                $errores[] = $datos['error'] ?? "Error en oferta '{$oferta->getNombre()}'";
                continue;
            }

            PedidoService::agregarOfertaAlCarrito(
                (int)$oferta_id,
                $oferta->getNombre(),
                $datos['veces'],
                $datos['descuento_total']
            );

            // consumir productos
            foreach ($datos['reserva'] as $id => $cantidad) {
                $pedido_productos[$id] -= $cantidad;
            }

            $ofertas_validas[] = $oferta_id;
        }

        $_SESSION['ofertas_seleccionadas'] = $ofertas_validas;

        return $errores;
    }

    public static function calcularDatosOferta($oferta, array $pedido_productos, array $itemsCarrito): ?array
    {
        $productos_oferta = ProductoDAO::getProductosDeOferta($oferta->getId());

        $veces = PHP_INT_MAX;
        $precio_pack = 0.0;
        $reserva = [];
        $faltantes = [];

        foreach ($productos_oferta as $po) {
            $id = $po->getId();
            $req = (int)$po->cantidad;
            $disponible = $pedido_productos[$id] ?? 0;

            if ($req > 0) {
                $veces = min($veces, intdiv($disponible, $req));
            } else {
                $veces = 0;
            }

            if ($disponible < $req) {
                $faltantes[] = $po->getNombre() .
                    " (necesitas {$req}, tienes {$disponible})";
            }

            $precio_pack += $po->getPrecioFinal() * $req;
        }

        if ($veces <= 0) {
            return [
                'error' => "Faltan productos en '{$oferta->getNombre()}': " . implode(', ', $faltantes)
            ];
        }

        foreach ($productos_oferta as $po) {
            $id = $po->getId();
            $reserva[$id] = (int)$po->cantidad * $veces;
        }

        $descuento_total = ($precio_pack * ($oferta->getDescuento() / 100)) * $veces;

        return [
            'veces' => $veces,
            'reserva' => $reserva,
            'descuento_total' => round($descuento_total, 2)
        ];
    }
}
