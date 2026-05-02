<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/Pedido.php';
require_once __DIR__ . '/../entities/ProductoPedido.php';

class PedidoDAO
{
    private static function hidratarPedido(array $row): Pedido
    {
        return new Pedido(
            $row['id'],
            $row['numero_pedido'],
            $row['fecha_hora'],
            $row['fecha'],
            $row['estado'],
            $row['tipo'],
            $row['metodo_pago'],
            $row['usuario_id'],
            $row['total_sin_descuentos'],
            $row['total_descuento'],
            $row['cocinero_id'],
            $row['total'] ?? null
        );
    }

    public static function obtenerSiguienteNumeroDelDia(): int
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT COALESCE(MAX(numero_pedido), 0) + 1 AS siguiente
             FROM pedidos
             WHERE DATE(fecha_hora) = CURDATE() AND estado != 'nuevo'"
        );
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        if ($result) {
            $result->free();
        }

        $stmt->close();

        return (int) ($row['siguiente'] ?? 1);
    }

    public static function guardarPedidoCompleto(
        int $usuario_id,
        string $metodo_pago,
        string $tipo,
        string $estado,
        array $lineas,
        array $ofertas,
        float $total_sin_descuentos,
        float $total_descuento
    ): int {
        global $conn;

        $requiereCocina = false;

        $conn->begin_transaction();

        try {
            $numero = self::obtenerSiguienteNumeroDelDia();
            $pedido_id = self::crearPedidoFormal($numero, $estado, $tipo, $metodo_pago, $usuario_id, $total_sin_descuentos, $total_descuento);

            require_once __DIR__ . '/ProductoDAO.php';
            require_once __DIR__ . '/OfertaEnPedidoDAO.php';

            foreach ($lineas as $clave => $item) {
                $producto_id = isset($item['producto_id']) ? (int) $item['producto_id'] : (int) $clave;
                
                $producto = ProductoDAO::getById((int) $producto_id);
                if ($producto && (int) $producto->getSeCocina() === 1) {
                    $requiereCocina = true;
                }

                $cantidad = (int) ($item['cantidad'] ?? 1);
                $precio_unitario = (float) ($item['precio_unitario'] ?? 0);
                $es_recompensa = !empty($item['es_recompensa']) ? 1 : 0;
                $bistrocoins_unitarios = (int) ($item['bistrocoins_unitarios'] ?? 0);

                for ($i = 0; $i < $cantidad; $i++) {
                    self::addProducto($pedido_id, (int) $producto_id, $precio_unitario, $es_recompensa, $bistrocoins_unitarios);
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

            if (!$requiereCocina && $estado === 'en_preparacion') {
                self::updateEstadoSimple($pedido_id, 'listo_cocina');
            }

            $conn->commit();
            return $pedido_id;

        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public static function crearPedidoFormal(
        int $numero,
        string $estado,
        string $tipo,
        string $metodo_pago,
        int $usuario_id,
        float $total_sin_descuentos,
        float $total_descuento
    ): int {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO pedidos (numero_pedido, fecha_hora, estado, tipo, metodo_pago, usuario_id, total_sin_descuentos, total_descuento)
             VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssidd", $numero, $estado, $tipo, $metodo_pago, $usuario_id, $total_sin_descuentos, $total_descuento);
        $stmt->execute();
        $pedido_id = (int) $conn->insert_id;
        $stmt->close();

        return $pedido_id;
    }

    public static function crearPedidoNuevo($usuario_id, $tipo)
    {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, tipo, estado) VALUES (?, ?, 'nuevo')");
        $stmt->bind_param("is", $usuario_id, $tipo);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function pedidoRequiereCocina(int $pedido_id): bool
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS cnt
             FROM productos_en_pedido pep
             JOIN productos p ON p.id = pep.producto_id
             WHERE pep.pedido_id = ? AND p.se_cocina = 1"
        );
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : ['cnt' => 0];

        if ($result) {
            $result->free();
        }

        $stmt->close();

        return ((int) ($row['cnt'] ?? 0)) > 0;
    }

    public static function getPedidoById($id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return self::hidratarPedido($row);
    }

    public static function getPedidoNuevo($usuario_id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? AND estado = 'nuevo' LIMIT 1");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        return $row ? self::hidratarPedido($row) : null;
    }

    public static function addProducto($pedido_id, $producto_id, $precio_unitario)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO productos_en_pedido (pedido_id, producto_id, precio_unitario, cantidad)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + 1"
        );
        $stmt->bind_param("iid", $pedido_id, $producto_id, $precio_unitario);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function updateCantidad($pedido_id, $producto_id, $cantidad)
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos_en_pedido SET cantidad = ? WHERE pedido_id = ? AND producto_id = ?");
        $stmt->bind_param("iii", $cantidad, $pedido_id, $producto_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function removeProducto($pedido_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM productos_en_pedido WHERE pedido_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $pedido_id, $producto_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function cancelarPedido($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT usuario_id, bistrocoins_liquidados, bistrocoins_generados, bistrocoins_gastados FROM pedidos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $result->free();
        $stmt->close();

        if ($pedido && (int)$pedido['bistrocoins_liquidados'] === 1) {
            $usuarioId = (int)$pedido['usuario_id'];
            $gastados = (int)$pedido['bistrocoins_gastados'];
            $generados = (int)$pedido['bistrocoins_generados'];
            
            require_once __DIR__ . '/UsuarioDAO.php';
            $saldo = UsuarioDAO::getBistrocoinsByUserId($usuarioId);
            $nuevoSaldo = max(0, $saldo + $gastados - $generados);

            $stmtUser = $conn->prepare("UPDATE usuarios SET bistrocoins = ?, updated_at = NOW() WHERE id = ?");
            $stmtUser->bind_param("ii", $nuevoSaldo, $usuarioId);
            $stmtUser->execute();
            $stmtUser->close();
        }

        $stmt = $conn->prepare("DELETE FROM productos_en_pedido WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    public static function getPedidosDeUsuario($usuario_id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? AND estado != 'nuevo' ORDER BY fecha_hora DESC");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $pedidos = [];

        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = self::hidratarPedido($fila);
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }

    public static function cambiarEstado(int $pedido_id, string $estado_nuevo): bool
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $estado_nuevo, $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
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
            $productos[] = new ProductoPedido(
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
        global $conn;

        $stmt = $conn->prepare(
            "SELECT p.*, u.nombre AS cliente_nombre, u.username
             FROM pedidos p
             LEFT JOIN usuarios u ON p.usuario_id = u.id
             WHERE p.estado = ?
             ORDER BY p.fecha_hora ASC"
        );
        $stmt->bind_param("s", $estado);
        $stmt->execute();

        $result = $stmt->get_result();
        $pedidos = [];
        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = $fila;
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }

    public static function marcarProductoPreparado($pedido_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos_en_pedido SET estado = 'preparado' WHERE pedido_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $pedido_id, $producto_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }


    public static function todosProductosBarraPreparados($pedido_id): bool
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS pendientes
             FROM productos_en_pedido pep
             JOIN productos p ON p.id = pep.producto_id
             WHERE pep.pedido_id = ?
               AND p.se_cocina = 0
               AND pep.estado = 'pendiente'"
        );
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : ['pendientes' => 0];

        if ($result) {
            $result->free();
        }

        $stmt->close();

        return ((int) ($row['pendientes'] ?? 0)) === 0;
    }

    public static function terminarPedidoParaEntrega($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE productos_en_pedido SET estado = 'terminado' WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $okLineas = $stmt->execute();
        $stmt->close();

        if (!$okLineas) {
            return false;
        }

        $stmt = $conn->prepare("UPDATE pedidos SET estado = 'terminado' WHERE id = ?");
        $stmt->bind_param("i", $pedido_id);
        $okPedido = $stmt->execute();
        $stmt->close();

        return $okPedido;
    }

    public static function getPedidosCocinando($cocinero_id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE cocinero_id = ? AND estado = 'cocinando' ORDER BY fecha_hora ASC");
        $stmt->bind_param("i", $cocinero_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $pedidos = $result->fetch_all(MYSQLI_ASSOC);

        $result->free();
        $stmt->close();

        return $pedidos;
    }

    public static function asignarCocineroYEstado($pedido_id, $cocinero_id, $estado)
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE pedidos SET cocinero_id = ?, estado = ? WHERE id = ?");
        $stmt->bind_param("isi", $cocinero_id, $estado, $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function getPedidosPendientesGerente()
    {
        global $conn;

        $query = "SELECT p.*, u.nombre AS cocinero_nombre, u.apellidos AS cocinero_apellidos, u.avatar_valor
                  FROM pedidos p LEFT JOIN usuarios u ON p.cocinero_id = u.id
                  WHERE p.estado IN ('recibido', 'en_preparacion', 'cocinando', 'listo_cocina', 'terminado')
                  ORDER BY p.fecha_hora ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute();

        $result = $stmt->get_result();
        $pedidos = [];
        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = $fila;
        }

        $result->free();
        $stmt->close();

        return $pedidos;
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
            WHERE usuario_id = ?
              AND estado != 'nuevo'
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

    public static function actualizarTotales($pedido_id, $total_sin_descuentos, $total_descuento)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE pedidos
             SET total_sin_descuentos = ?, total_descuento = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ddi", $total_sin_descuentos, $total_descuento, $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function limpiarOfertas($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM ofertas_en_pedido WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function getOfertasDePedido($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT oep.*, o.nombre
             FROM ofertas_en_pedido oep
             JOIN ofertas o ON o.id = oep.oferta_id
             WHERE oep.pedido_id = ?"
        );

        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $result = $stmt->get_result();

        $ofertas = [];

        while ($fila = $result->fetch_assoc()) {
            $ofertas[] = (object) [
                'id' => $fila['id'],
                'pedido_id' => $fila['pedido_id'],
                'oferta_id' => $fila['oferta_id'],
                'nombre' => $fila['nombre'],
                'veces_aplicada' => $fila['veces_aplicada'],
                'descuento_total' => $fila['descuento_total'],
            ];
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    public static function contarPedidosActivosByUsuario(int $usuario_id): int
    {
        global $conn;

        $sql = "
            SELECT COUNT(*) as num_activos
            FROM pedidos
            WHERE usuario_id = ?
               AND estado IN ('en_preparacion', 'cocinando', 'listo_cocina', 'terminado')
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        return (int) ($row['num_activos'] ?? 0);
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
        require_once __DIR__ . '/UsuarioDAO.php';
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

    public static function getEstadoActualPedido(int $pedido_id): ?string
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

    public static function updateEstadoSimple(int $pedido_id, string $estado_nuevo): bool
    {
        global $conn;

        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $estado_nuevo, $pedido_id);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}