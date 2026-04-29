<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/producto.php';
require_once __DIR__ . '/../entities/productos_Pedido.php';
require_once __DIR__ . '/../entities/pedido.php';
require_once __DIR__ . '/../includes/UsuarioDAO.php';
require_once __DIR__ . '/../includes/RecompensaDAO.php';
require_once __DIR__ . '/../includes/ProductoDAO.php';
require_once __DIR__ . '/../includes/ofertaEnPedidoDAO.php';
require_once __DIR__ . '/../includes/PedidoDAO.php';

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

    public static function agregarProductoAlCarrito(int $producto_id, float $precio_unitario, int $cantidad = 1): void
    {
        self::asegurarCarritoSesion();

        if (!isset($_SESSION['carrito']['items'][$producto_id])) {
            $_SESSION['carrito']['items'][$producto_id] = [
                'cantidad' => 0,
                'precio_unitario' => $precio_unitario,
            ];
        }

        $_SESSION['carrito']['items'][$producto_id]['cantidad'] += max(1, $cantidad);
        $_SESSION['carrito']['items'][$producto_id]['precio_unitario'] = $precio_unitario;
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

    public static function eliminarProductoDelCarrito(int $producto_id): void
    {
        self::asegurarCarritoSesion();
        unset($_SESSION['carrito']['items'][$producto_id]);
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
        global $conn;

        self::asegurarCarritoSesion();
        $carrito = $_SESSION['carrito'];

        if (empty($carrito['items']) || empty($carrito['tipo'])) {
            return null;
        }

        $lineas = $carrito['items'];
        $ofertas = $carrito['ofertas'];
        $tipo = (string) $carrito['tipo'];

        $estado = ($metodo_pago === 'tarjeta') ? 'en_preparacion' : 'recibido';
        $requiereCocina = false;

        $conn->begin_transaction();

        try {
            $numero = PedidoDAO::obtenerSiguienteNumeroDelDia();
            $pedido_id = PedidoDAO::crearPedidoFormal(
                $numero,
                $estado,
                $tipo,
                $metodo_pago,
                $usuario_id,
                $total_sin_descuentos,
                $total_descuento
            );

            foreach ($lineas as $producto_id => $item) {
                $producto = ProductoDAO::getById((int) $producto_id);
                if ($producto && (int) $producto->getSeCocina() === 1) {
                    $requiereCocina = true;
                }

                $cantidad = (int) ($item['cantidad'] ?? 1);
                $precio_unitario = (float) ($item['precio_unitario'] ?? 0);

                for ($i = 0; $i < $cantidad; $i++) {
                    PedidoDAO::addProducto($pedido_id, (int) $producto_id, $precio_unitario);
                }
            }

            foreach ($ofertas as $oferta) {
                OfertaEnPedidoDAO::addOferta(
                    $pedido_id,
                    (int) ($oferta['oferta_id'] ?? 0),
                    (int) ($oferta['veces_aplicada'] ?? 0),
                    (float) ($oferta['descuento_total'] ?? 0)
                );
            }

            if (!$requiereCocina) {
                PedidoDAO::terminarPedidoParaEntrega($pedido_id);
            }

            $conn->commit();

            $_SESSION['ultimo_pedido_id'] = $pedido_id;
            self::limpiarCarrito();

            return $pedido_id;
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public static function crearPedido($usuario_id, $tipo)
    {
        return PedidoDAO::crearPedidoNuevo($usuario_id, $tipo);
    }

    public static function getPedidoNuevo($usuario_id)
    {
        return PedidoDAO::getPedidoNuevo($usuario_id);
    }

    public static function addProducto($pedido_id, $producto_id, $precio_unitario, $esRecompensa = 0, $bistrocoinsUnitarios = 0)
    {
        global $conn;

        $esRecompensa = (int) $esRecompensa;
        $bistrocoinsUnitarios = (int) $bistrocoinsUnitarios;

        $stmt = $conn->prepare(
            "INSERT INTO productos_en_pedido 
                (pedido_id, producto_id, precio_unitario, cantidad, es_recompensa, bistrocoins_unitarios)
             VALUES (?, ?, ?, 1, ?, ?)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + 1"
        );
        $stmt->bind_param("iidii", $pedido_id, $producto_id, $precio_unitario, $esRecompensa, $bistrocoinsUnitarios);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function addRecompensaAPedido(int $pedido_id, int $recompensa_id, int $usuario_id): array
    {
        $recompensa = RecompensaDAO::getById($recompensa_id);
        if (!$recompensa || !$recompensa->isActiva()) {
            return [false, 'La recompensa seleccionada no existe o no está activa.'];
        }

        $saldo = UsuarioDAO::getBistrocoinsByUserId($usuario_id);
        $gastados = self::getBistrocoinsGastadosPedido($pedido_id);
        $disponibles = $saldo - $gastados;

        if ($disponibles < $recompensa->getBistrocoins()) {
            return [false, 'No tienes BistroCoins suficientes para añadir esta recompensa al pedido.'];
        }

        $ok = self::addProducto(
            $pedido_id,
            (int) $recompensa->getProductoId(),
            0.0,
            1,
            (int) $recompensa->getBistrocoins()
        );

        return [$ok, $ok ? 'Recompensa añadida al pedido.' : 'No se pudo añadir la recompensa al pedido.'];
    }

    public static function updateCantidad($pedido_id, $producto_id, $cantidad)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE productos_en_pedido 
             SET cantidad = ?
             WHERE pedido_id = ? AND producto_id = ? AND es_recompensa = 0"
        );
        $stmt->bind_param("iii", $cantidad, $pedido_id, $producto_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function updateCantidadByLinea(int $linea_id, int $cantidad)
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos_en_pedido SET cantidad = ? WHERE id = ?");
        $stmt->bind_param("ii", $cantidad, $linea_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function removeProducto($pedido_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM productos_en_pedido 
             WHERE pedido_id = ? AND producto_id = ? AND es_recompensa = 0"
        );
        $stmt->bind_param("ii", $pedido_id, $producto_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function removeProductoByLinea(int $linea_id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM productos_en_pedido WHERE id = ?");
        $stmt->bind_param("i", $linea_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function cancelarPedido($pedido_id)
    {
        return PedidoDAO::cancelarPedido($pedido_id);
    }

    public static function confirmarPedido($pedido_id, $metodo_pago, $total)
    {
        global $conn;

        $estadoOriginal = self::getEstadoActualPedido((int) $pedido_id) ?? 'nuevo';

        $stmtNumero = $conn->prepare("
            SELECT COALESCE(MAX(numero_pedido), 0) + 1 AS siguiente
            FROM pedidos
            WHERE DATE(fecha_hora) = CURDATE() AND estado != 'nuevo'
        ");
        $stmtNumero->execute();
        $rs = $stmtNumero->get_result();
        $numero = (int) ($rs->fetch_assoc()['siguiente'] ?? 1);
        $rs->free();
        $stmtNumero->close();

        $estado = ($metodo_pago === 'tarjeta') ? 'en_preparacion' : 'recibido';

        $stmtCheck = $conn->prepare(
            "SELECT COUNT(*) AS cnt
             FROM productos_en_pedido pep
             JOIN productos p ON p.id = pep.producto_id
             WHERE pep.pedido_id = ? AND p.se_cocina = 1"
        );
        $stmtCheck->bind_param("i", $pedido_id);
        $stmtCheck->execute();
        $rsCheck = $stmtCheck->get_result();
        $rowCheck = $rsCheck->fetch_assoc();
        $rsCheck->free();
        $stmtCheck->close();

        $terminarPedido = ((int) ($rowCheck['cnt'] ?? 0) === 0);

        if ($terminarPedido) {
            $estado = 'terminado';
        }

        $stmt = $conn->prepare(
            "UPDATE pedidos
             SET estado = ?, numero_pedido = ?, metodo_pago = ?, fecha_hora = CURRENT_TIMESTAMP
             WHERE id = ?"
        );
        $stmt->bind_param("sisi", $estado, $numero, $metodo_pago, $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            return false;
        }

        if ($metodo_pago === 'tarjeta') {
            $okCoins = self::liquidarBistroCoinsSiProcede((int) $pedido_id);

            if (!$okCoins) {
                $stmtRollback = $conn->prepare(
                    "UPDATE pedidos
                     SET estado = ?, numero_pedido = NULL, metodo_pago = NULL
                     WHERE id = ?"
                );
                $stmtRollback->bind_param("si", $estadoOriginal, $pedido_id);
                $stmtRollback->execute();
                $stmtRollback->close();

                return false;
            }
        }

        if ($terminarPedido) {
            self::terminarPedidoParaEntrega($pedido_id);
        }

        return true;
    }

    public static function getPedidoById($id)
    {
        return PedidoDAO::getPedidoById($id);
    }

    public static function getPedidosDeUsuario($usuario_id)
    {
        return PedidoDAO::getPedidosDeUsuario($usuario_id);
    }

    private static function getEstadoActualPedido(int $pedido_id): ?string
    {
        global $conn;

        $stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        $stmt->close();

        return $row['estado'] ?? null;
    }

    private static function updateEstadoSimple(int $pedido_id, string $estado_nuevo): bool
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $estado_nuevo, $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function cambiarEstado(int $pedido_id, string $estado_nuevo): bool
    {
        $estadoAnterior = self::getEstadoActualPedido($pedido_id);

        if ($estadoAnterior === null) {
            return false;
        }

        $ok = self::updateEstadoSimple($pedido_id, $estado_nuevo);
        if (!$ok) {
            return false;
        }

        if ($estado_nuevo === 'en_preparacion') {
            $okCoins = self::liquidarBistroCoinsSiProcede($pedido_id);

            if (!$okCoins) {
                self::updateEstadoSimple($pedido_id, $estadoAnterior);
                return false;
            }
        }

        return true;
    }

    public static function getProductosPedido($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT pep.*, p.nombre, p.imagen, p.se_cocina
             FROM productos_en_pedido pep
             JOIN productos p ON p.id = pep.producto_id
             WHERE pep.pedido_id = ?
             ORDER BY pep.es_recompensa ASC, pep.id ASC"
        );
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $productos = [];

        while ($fila = $result->fetch_assoc()) {
            $productos[] = new Productos_Pedido(
                $fila['id'],
                $fila['nombre'],
                $fila['pedido_id'],
                $fila['producto_id'],
                $fila['precio_unitario'],
                $fila['cantidad'],
                $fila['estado'],
                $fila['imagen'],
                $fila['se_cocina'] ?? 1,
                $fila['es_recompensa'] ?? 0,
                $fila['bistrocoins_unitarios'] ?? 0
            );
        }

        $result->free();
        $stmt->close();

        return $productos;
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
        global $conn;

        $sql = "
            SELECT id, numero_pedido, estado, fecha_hora, total
            FROM pedidos
            WHERE usuario_id = ?
              AND estado IN ('en_preparacion', 'cocinando', 'listo_cocina', 'terminado')
            ORDER BY fecha_hora DESC
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $row['lineas'] = self::getResumenLineasPedido((int) $row['id']);
            $pedidos[] = $row;
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }

    public static function getPedidosHistoricoByUsuario(int $usuario_id): array
    {
        global $conn;

        $sql = "
            SELECT id, numero_pedido, fecha_hora, tipo, total, estado, bistrocoins_generados, bistrocoins_gastados
            FROM pedidos
            WHERE usuario_id = ? AND estado != 'nuevo'
            ORDER BY fecha_hora DESC
            LIMIT 15
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $row['lineas'] = self::getResumenLineasPedido((int) $row['id']);
            $pedidos[] = $row;
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }

    public static function getResumenLineasPedido(int $pedido_id): array
    {
        global $conn;

        $sql = "SELECT p.nombre, pep.cantidad, pep.es_recompensa
                FROM productos_en_pedido pep
                JOIN productos p ON p.id = pep.producto_id
                WHERE pep.pedido_id = ?
                ORDER BY pep.es_recompensa ASC, p.nombre ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $lineas = [];
        while ($row = $result->fetch_assoc()) {
            $lineas[] = $row;
        }

        $result->free();
        $stmt->close();

        return $lineas;
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

    public static function getBistrocoinsGastadosPedido(int $pedido_id): int
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(cantidad * bistrocoins_unitarios), 0) AS total
             FROM productos_en_pedido
             WHERE pedido_id = ? AND es_recompensa = 1"
        );
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        $stmt->close();

        return (int) ($row['total'] ?? 0);
    }

    public static function liquidarBistroCoinsSiProcede(int $pedido_id): bool
    {
        global $conn;

        $stmt = $conn->prepare("SELECT usuario_id, total, bistrocoins_liquidados FROM pedidos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $result->free();
        $stmt->close();

        if (!$pedido) {
            return false;
        }

        if ((int) $pedido['bistrocoins_liquidados'] === 1) {
            return true;
        }

        $usuarioId = (int) $pedido['usuario_id'];
        $gastados = self::getBistrocoinsGastadosPedido($pedido_id);
        $generados = (int) floor(max(0, (float) $pedido['total']));
        $saldo = UsuarioDAO::getBistrocoinsByUserId($usuarioId);

        if ($saldo < $gastados) {
            return false;
        }

        $nuevoSaldo = $saldo - $gastados + $generados;

        $conn->begin_transaction();

        try {
            $stmtUser = $conn->prepare("UPDATE usuarios SET bistrocoins = ?, updated_at = NOW() WHERE id = ?");
            $stmtUser->bind_param("ii", $nuevoSaldo, $usuarioId);
            $stmtUser->execute();
            $stmtUser->close();

            $stmtPedido = $conn->prepare(
                "UPDATE pedidos
                 SET bistrocoins_generados = ?, bistrocoins_gastados = ?, bistrocoins_liquidados = 1
                 WHERE id = ?"
            );
            $stmtPedido->bind_param("iii", $generados, $gastados, $pedido_id);
            $stmtPedido->execute();
            $stmtPedido->close();

            $conn->commit();
            return true;
        } catch (Throwable $e) {
            $conn->rollback();
            return false;
        }
    }
}