<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/Producto.php';
require_once __DIR__ . '/../includes/ProductoDAO.php';
require_once __DIR__ . '/../includes/OfertaEnPedidoDAO.php';
require_once __DIR__ . '/../includes/PedidoDAO.php';
require_once __DIR__ . '/OfertaService.php';

class PedidoService
{
    private static function asegurarCarritoSesion(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (!isset($_SESSION['carrito']['tipo'])) {
            $_SESSION['carrito']['tipo'] = null;
        }

        if (!isset($_SESSION['carrito']['items']) || !is_array($_SESSION['carrito']['items'])) {
            $_SESSION['carrito']['items'] = [];
        }

        if (!isset($_SESSION['carrito']['ofertas']) || !is_array($_SESSION['carrito']['ofertas'])) {
            $_SESSION['carrito']['ofertas'] = [];
        }
    }

    public static function iniciarCarrito(string $tipo): void
    {
        self::asegurarCarritoSesion();

        $_SESSION['carrito'] = [
            'tipo' => $tipo,
            'items' => [],
            'ofertas' => [],
        ];

        unset($_SESSION['ultimo_pedido_id']);
        unset($_SESSION['ofertas_seleccionadas']);
    }

    public static function getTipoCarrito(): ?string
    {
        self::asegurarCarritoSesion();

        return $_SESSION['carrito']['tipo'] ?? null;
    }

    public static function carritoTieneTipo(): bool
    {
        return self::getTipoCarrito() !== null;
    }

    public static function getCarritoItems(): array
    {
        self::asegurarCarritoSesion();

        return $_SESSION['carrito']['items'];
    }

    public static function carritoTieneProductos(): bool
    {
        return !empty(self::getCarritoItems());
    }

    public static function agregarProductoAlCarrito(int $producto_id, float $precio_unitario, int $cantidad = 1, bool $es_recompensa = false, int $bistrocoins_unitarios = 0): void
    {
        self::asegurarCarritoSesion();

        $clave_carrito = $es_recompensa ? $producto_id . '_R' : $producto_id;

        if (!isset($_SESSION['carrito']['items'][$clave_carrito])) {
            $_SESSION['carrito']['items'][$clave_carrito] = [
                'producto_id' => $producto_id,
                'cantidad' => 0,
                'precio_unitario' => $precio_unitario,
                'es_recompensa' => $es_recompensa,
                'bistrocoins_unitarios' => $bistrocoins_unitarios
            ];
        }

        $_SESSION['carrito']['items'][$clave_carrito]['cantidad'] += max(1, $cantidad);
        $_SESSION['carrito']['items'][$clave_carrito]['precio_unitario'] = $precio_unitario;
    }

    public static function addRecompensaAlCarrito(int $recompensa_id, int $usuario_id): array
    {
        require_once __DIR__ . '/RecompensaDAO.php';
        require_once __DIR__ . '/UsuarioDAO.php';

        $recompensa = RecompensaDAO::getById($recompensa_id);

        if (!$recompensa || !$recompensa->isActiva()) {
            return [false, 'La recompensa seleccionada no existe o no está activa.'];
        }

        $saldo = UsuarioDAO::getBistrocoinsByUserId($usuario_id);

        self::asegurarCarritoSesion();
        $gastados = 0;
        foreach ($_SESSION['carrito']['items'] as $item) {
            if (!empty($item['es_recompensa'])) {
                $gastados += ((int)$item['bistrocoins_unitarios']) * ((int)$item['cantidad']);
            }
        }

        $disponibles = $saldo - $gastados;

        if ($disponibles < $recompensa->getBistrocoins()) {
            return [false, 'No tienes BistroCoins suficientes para añadir esta recompensa al pedido.'];
        }

        self::agregarProductoAlCarrito(
            (int) $recompensa->getProductoId(),
            0.0,
            1,
            true,
            (int) $recompensa->getBistrocoins()
        );

        return [true, 'Recompensa añadida al pedido.'];
    }

    public static function actualizarCantidadCarrito(int $producto_id, int $cantidad): void
    {
        self::asegurarCarritoSesion();

        if ($cantidad <= 0) {
            unset($_SESSION['carrito']['items'][$producto_id]);
            return;
        }

        if (!isset($_SESSION['carrito']['items'][$producto_id])) {
            return;
        }

        $_SESSION['carrito']['items'][$producto_id]['cantidad'] = $cantidad;
    }

    public static function eliminarProductoDelCarrito($clave_carrito): void
    {
        self::asegurarCarritoSesion();
        unset($_SESSION['carrito']['items'][$clave_carrito]);
    }

    public static function limpiarCarrito(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        unset($_SESSION['carrito']);
        unset($_SESSION['errores_ofertas']);
    }

    public static function getCarritoOfertas(): array
    {
        self::asegurarCarritoSesion();

        $itemsCarrito = self::getCarritoItems();

        foreach ($_SESSION['carrito']['ofertas'] as $key => $item) {

            $oferta = \OfertaDAO::getById($item['oferta_id']);

            // oferta borrada
            if (!$oferta) {
                unset($_SESSION['carrito']['ofertas'][$key]);
                continue;
            }

            // oferta caducada
            if (!$oferta->estaActiva()) {
                unset($_SESSION['carrito']['ofertas'][$key]);
                continue;
            }

            // ACTUALIZAR NOMBRE (esto sí es correcto)
            $_SESSION['carrito']['ofertas'][$key]['nombre'] = $oferta->getNombre();

            // RECALCULAR DESCUENTO REAL
            $descuentoCalculado = self::calcularDescuentoOferta($oferta, $itemsCarrito);

            if ($descuentoCalculado <= 0) {
                unset($_SESSION['carrito']['ofertas'][$key]);
                continue;
            }

            $_SESSION['carrito']['ofertas'][$key]['descuento_total'] = $descuentoCalculado;
        }

        $_SESSION['carrito']['ofertas'] = array_values($_SESSION['carrito']['ofertas']);

        // $ofertas = $_SESSION['ofertas_seleccionadas'] ?? [];

        // $errores = \OfertaService::aplicarOfertas($ofertas);

        // $_SESSION['errores_ofertas'] = $errores;

        return $_SESSION['carrito']['ofertas'];
    }

    public static function limpiarOfertasCarrito(): void
    {
        self::asegurarCarritoSesion();
        $_SESSION['carrito']['ofertas'] = [];
    }

    public static function agregarOfertaAlCarrito(int $oferta_id, string $nombre, int $veces, float $descuento_total): void
    {
        self::asegurarCarritoSesion();

        $_SESSION['carrito']['ofertas'][] = [
            'oferta_id' => $oferta_id,
            'nombre' => $nombre,
            'veces_aplicada' => $veces,
            'descuento_total' => $descuento_total,
        ];
    }

    public static function calcularTotalCarritoSinDescuentos(): float
    {
        $total = 0.0;

        foreach (self::getCarritoItems() as $item) {
            $total += ((float) $item['precio_unitario']) * ((int) $item['cantidad']);
        }

        return round($total, 2);
    }

    public static function calcularDescuentoCarrito(): float
    {
        $total = 0.0;

        foreach (self::getCarritoOfertas() as $oferta) {
            $total += (float) ($oferta['descuento_total'] ?? 0);
        }

        return round($total, 2);
    }

    public static function confirmarCarrito(int $usuario_id, string $metodo_pago, float $total_sin_descuentos, float $total_descuento): ?int
    {
        self::asegurarCarritoSesion();
        $carrito = $_SESSION['carrito'];

        if (empty($carrito['items']) || empty($carrito['tipo'])) {
            return null;
        }

        $lineas = $carrito['items'];
        $ofertas = $carrito['ofertas'];
        $tipo = (string) $carrito['tipo'];

        $estado = ($metodo_pago === 'tarjeta') ? 'en_preparacion' : 'recibido';

        $pedido_id = PedidoDAO::guardarPedidoCompleto($usuario_id, $metodo_pago, $tipo, $estado, $lineas, $ofertas, $total_sin_descuentos, $total_descuento);

        $_SESSION['ultimo_pedido_id'] = $pedido_id;
        self::limpiarCarrito();

        return $pedido_id;
    }

    public static function crearPedido($usuario_id, $tipo)
    {
        return PedidoDAO::crearPedidoNuevo($usuario_id, $tipo);
    }

    public static function getPedidoNuevo($usuario_id)
    {
        return PedidoDAO::getPedidoNuevo($usuario_id);
    }

    public static function addProducto($pedido_id, $producto_id, $precio_unitario, $es_recompensa = 0, $bistrocoins_unitarios = 0)
    {
        return PedidoDAO::addProducto($pedido_id, $producto_id, $precio_unitario, $es_recompensa, $bistrocoins_unitarios);
    }

    public static function updateCantidad($pedido_id, $producto_id, $cantidad)
    {
        return PedidoDAO::updateCantidad($pedido_id, $producto_id, $cantidad);
    }

    public static function removeProducto($pedido_id, $producto_id)
    {
        return PedidoDAO::removeProducto($pedido_id, $producto_id);
    }

    public static function cancelarPedido($pedido_id)
    {
        return PedidoDAO::cancelarPedido($pedido_id);
    }


    public static function getPedidoById($id)
    {
        return PedidoDAO::getPedidoById($id);
    }

    public static function getPedidosDeUsuario($usuario_id)
    {
        return PedidoDAO::getPedidosDeUsuario($usuario_id);
    }

    public static function cambiarEstado(int $pedido_id, string $estado_nuevo): bool
    {
        $estadoAnterior = PedidoDAO::getEstadoActualPedido($pedido_id);

        if ($estadoAnterior === null) {
            return false;
        }

        $ok = PedidoDAO::updateEstadoSimple($pedido_id, $estado_nuevo);
        if (!$ok) {
            return false;
        }

        if ($estado_nuevo === 'en_preparacion') {
            $okCoins = PedidoDAO::liquidarBistroCoinsSiProcede($pedido_id);

            if (!$okCoins) {
                PedidoDAO::updateEstadoSimple($pedido_id, $estadoAnterior);
                return false;
            }

            $requiereCocina = false;
            $prods = PedidoDAO::getProductosPedido($pedido_id);
            foreach ($prods as $pr) {
                if ((int)$pr->getSeCocina() === 1) {
                    $requiereCocina = true;
                    break;
                }
            }

            if (!$requiereCocina) {
                PedidoDAO::updateEstadoSimple($pedido_id, 'listo_cocina');
            }
        }

        return true;
    }

    public static function getProductosPedido($pedido_id)
    {
        return PedidoDAO::getProductosPedido($pedido_id);
    }

    public static function getPedidosPorEstado(string $estado): array
    {
        return PedidoDAO::getPedidosPorEstado($estado);
    }

    public static function marcarProductoPreparado($pedido_id, $producto_id)
    {
        return PedidoDAO::marcarProductoPreparado($pedido_id, $producto_id);
    }

    public static function marcarProductoPreparadoCamarero($pedido_id, $producto_id)
    {
        $ok = PedidoDAO::marcarProductoPreparado($pedido_id, $producto_id);

        if ($ok && PedidoDAO::todosProductosBarraPreparados($pedido_id)) {
            PedidoDAO::terminarPedidoParaEntrega($pedido_id);
        }

        return $ok;
    }

    public static function terminarPedidoParaEntrega($pedido_id)
    {
        return PedidoDAO::terminarPedidoParaEntrega($pedido_id);
    }

    public static function getPedidosCocinando($cocinero_id)
    {
        return PedidoDAO::getPedidosCocinando($cocinero_id);
    }

    public static function asignarCocineroYEstado($pedido_id, $cocinero_id, $estado)
    {
        return PedidoDAO::asignarCocineroYEstado($pedido_id, $cocinero_id, $estado);
    }

    public static function getPedidosPendientesGerente()
    {
        return PedidoDAO::getPedidosPendientesGerente();
    }

    public static function getPedidosActivosByUsuario(int $usuario_id): array
    {
        return PedidoDAO::getPedidosActivosByUsuario($usuario_id);
    }

    public static function getPedidosHistoricoByUsuario(int $usuario_id): array
    {
        return PedidoDAO::getPedidosHistoricoByUsuario($usuario_id);
    }

    public static function actualizarTotales($pedido_id, $total_sin_descuentos, $total_descuento)
    {
        return PedidoDAO::actualizarTotales($pedido_id, $total_sin_descuentos, $total_descuento);
    }

    public static function limpiarOfertas($pedido_id)
    {
        return PedidoDAO::limpiarOfertas($pedido_id);
    }

    public static function getOfertasDePedido($pedido_id)
    {
        return PedidoDAO::getOfertasDePedido($pedido_id);
    }

    public static function contarPedidosActivosByUsuario(int $usuario_id): int
    {
        return PedidoDAO::contarPedidosActivosByUsuario($usuario_id);
    }

    public static function getResumenLineasPedido(int $pedido_id): array
    {
        return PedidoDAO::getResumenLineasPedido($pedido_id);
    }

    public static function getBistrocoinsGastadosPedido(int $pedido_id): int
    {
        return PedidoDAO::getBistrocoinsGastadosPedido($pedido_id);
    }

    public static function liquidarBistroCoinsSiProcede(int $pedido_id): bool
    {
        return PedidoDAO::liquidarBistroCoinsSiProcede($pedido_id);
    }

    public static function getEstadoActualPedido(int $pedido_id): ?string
    {
        return PedidoDAO::getEstadoActualPedido($pedido_id);
    }

    public static function updateEstadoSimple(int $pedido_id, string $estado_nuevo): bool
    {
        return PedidoDAO::updateEstadoSimple($pedido_id, $estado_nuevo);
    }


    private static function calcularDescuentoOferta($oferta, $itemsCarrito): float
    {
        // reconstruir mapa producto_id => cantidad
        $pedido_productos = [];

        foreach ($itemsCarrito as $item) {
            if (!empty($item['es_recompensa'])) continue;

            $pid = $item['producto_id'];
            $pedido_productos[$pid] = ($pedido_productos[$pid] ?? 0) + (int)$item['cantidad'];
        }

        $datos = OfertaService::calcularDatosOferta($oferta, $pedido_productos, $itemsCarrito);

        return $datos['descuento_total'] ?? 0.0;
    }

    // private static function calcularDescuentoOferta($oferta, $itemsCarrito): float
    // {
    //     $productosOferta = \ProductoDAO::getProductosDeOferta($oferta->getId());

    //     // map carrito: producto_id => cantidad
    //     $carritoCantidades = [];
    //     $carritoPrecios = [];

    //     foreach ($itemsCarrito as $item) {
    //         $id = $item['producto_id'];
    //         $carritoCantidades[$id] = ($carritoCantidades[$id] ?? 0) + $item['cantidad'];
    //         $carritoPrecios[$id] = $item['precio_unitario'];
    //     }

    //     // calcular cuántas veces se puede aplicar la oferta
    //     $veces = PHP_INT_MAX;

    //     foreach ($productosOferta as $po) {
    //         $id = $po->getId();
    //         $req = $po->cantidad;
    //         $disponible = $carritoCantidades[$id] ?? 0;

    //         $veces = min($veces, intdiv($disponible, $req));
    //     }

    //     if ($veces <= 0) {
    //         return 0.0;
    //     }

    //     // calcular total SOLO de productos usados en la oferta
    //     $totalOferta = 0.0;

    //     foreach ($productosOferta as $po) {
    //         $id = $po->getId();
    //         $req = $po->cantidad;

    //         $cantidadUsada = $req * $veces;
    //         $precio = $carritoPrecios[$id] ?? 0;

    //         $totalOferta += $precio * $cantidadUsada;
    //     }

    //     $descuento = $oferta->getDescuento();

    //     return round($totalOferta * ($descuento / 100), 2);
    // }
}
