<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/pedido.php';

class PedidoService
{
    public static function crearPedido($usuario_id, $tipo)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO pedidos (usuario_id, tipo, estado) VALUES (?, ?, 'nuevo')"
        );
        $stmt->bind_param("is", $usuario_id, $tipo);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function getPedidoNuevo($usuario_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM pedidos WHERE usuario_id = ? AND estado = 'nuevo' LIMIT 1"
        );
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return new Pedido(
            $row['id'],
            $row['numero_pedido'] ?? null,
            $row['fecha_hora'] ?? null,
            $row['fecha'] ?? null,
            $row['estado'],
            $row['tipo'],
            $row['metodo_pago'] ?? null,
            $row['usuario_id'],
            $row['total'] ?? 0,
            0,
            $row['cocinero_id'] ?? null
        );
    }

    public static function getPedidoById($id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) {
            return null;
        }

        return new Pedido(
            $row['id'],
            $row['numero_pedido'] ?? null,
            $row['fecha_hora'] ?? null,
            $row['fecha'] ?? null,
            $row['estado'],
            $row['tipo'],
            $row['metodo_pago'] ?? null,
            $row['usuario_id'],
            $row['total'] ?? 0,
            0,
            $row['cocinero_id'] ?? null
        );
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

        $stmt = $conn->prepare(
            "UPDATE productos_en_pedido
             SET cantidad = ?
             WHERE pedido_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("iii", $cantidad, $pedido_id, $producto_id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function removeProducto($pedido_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM productos_en_pedido WHERE pedido_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("ii", $pedido_id, $producto_id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function cancelarPedido($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM productos_en_pedido WHERE pedido_id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();
    }

    public static function confirmarPedido($pedido_id, $metodo_pago, $total)
    {
        global $conn;

        $rs = $conn->query(
            "SELECT COALESCE(MAX(numero_pedido), 0) + 1 AS siguiente
             FROM pedidos
             WHERE DATE(fecha_hora) = CURDATE() AND estado <> 'nuevo'"
        );
        $fila = $rs->fetch_assoc();
        $numero = (int)$fila['siguiente'];
        $rs->free();

        $estado = ($metodo_pago === 'tarjeta') ? 'en_preparacion' : 'recibido';

        $stmt = $conn->prepare(
            "UPDATE pedidos
             SET estado = ?, numero_pedido = ?, metodo_pago = ?, total = ?, fecha_hora = CURRENT_TIMESTAMP
             WHERE id = ?"
        );
        $stmt->bind_param("sisdi", $estado, $numero, $metodo_pago, $total, $pedido_id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
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
            "SELECT pep.*, p.nombre, p.imagen, p.iva
             FROM productos_en_pedido pep
             JOIN productos p ON p.id = pep.producto_id
             WHERE pep.pedido_id = ?"
        );
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $result->free();
        $stmt->close();

        return $data;
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
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $result->free();
        $stmt->close();

        return $data;
    }

    public static function getPedidosDeUsuario(int $usuario_id): array
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM pedidos
             WHERE usuario_id = ? AND estado <> 'nuevo'
             ORDER BY fecha_hora DESC"
        );
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $pedidos = [];

        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = new Pedido(
                $fila['id'],
                $fila['numero_pedido'] ?? null,
                $fila['fecha_hora'] ?? null,
                $fila['fecha'] ?? null,
                $fila['estado'],
                $fila['tipo'],
                $fila['metodo_pago'] ?? null,
                $fila['usuario_id'],
                $fila['total'] ?? 0,
                0,
                $fila['cocinero_id'] ?? null
            );
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }

    public static function marcarProductoPreparado($pedido_id, $producto_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE productos_en_pedido
             SET estado = 'preparado'
             WHERE pedido_id = ? AND producto_id = ?"
        );
        $stmt->bind_param("ii", $pedido_id, $producto_id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function getPedidosCocinando($cocinero_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM pedidos
             WHERE cocinero_id = ? AND estado = 'cocinando'
             ORDER BY fecha_hora ASC"
        );
        $stmt->bind_param("i", $cocinero_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $result->free();
        $stmt->close();

        return $data;
    }

    public static function asignarCocineroYEstado($pedido_id, $cocinero_id, $estado)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE pedidos SET cocinero_id = ?, estado = ? WHERE id = ?"
        );
        $stmt->bind_param("isi", $cocinero_id, $estado, $pedido_id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function getPedidosPendientesGerente()
    {
        global $conn;

        $query = "SELECT p.*, u.nombre AS cocinero_nombre, u.apellidos AS cocinero_apellidos, u.avatar_valor
                  FROM pedidos p
                  LEFT JOIN usuarios u ON p.cocinero_id = u.id
                  WHERE p.estado IN ('recibido', 'en_preparacion', 'cocinando', 'listo_cocina', 'terminado')
                  ORDER BY p.fecha_hora ASC";

        $rs = $conn->query($query);
        $data = $rs->fetch_all(MYSQLI_ASSOC);
        $rs->free();

        return $data;
    }

    public static function getPedidosActivosByUsuario(int $usuario_id): array
    {
        global $conn;

        $sql = "
            SELECT numero_pedido, estado, fecha_hora, total
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
            SELECT numero_pedido, fecha_hora, tipo, total, estado
            FROM pedidos
            WHERE usuario_id = ?
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
            $pedidos[] = $row;
        }

        $result->free();
        $stmt->close();

        return $pedidos;
    }
}