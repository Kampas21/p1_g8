<?php
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/../entities/producto.php';
require_once __DIR__ . '/../entities/productos_Pedido.php';
require_once __DIR__ . '/../entities/pedido.php';
require_once __DIR__ . '/../includes/UsuarioDAO.php';
require_once __DIR__ . '/../includes/RecompensaDAO.php';

class PedidoService
{

    /**
     * Crea un pedido nuevo en estado 'nuevo' y devuelve su id.
     */
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

    /**
     * Obtiene el pedido en estado 'nuevo' del usuario, o null si no tiene ninguno.
     */
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

    public static function addProducto($pedido_id, $producto_id, $precio_unitario, $esRecompensa = 0, $bistrocoinsUnitarios = 0)
    {
        global $conn;
        $esRecompensa = (int)$esRecompensa;
        $bistrocoinsUnitarios = (int)$bistrocoinsUnitarios;
        $stmt = $conn->prepare(
            "INSERT INTO productos_en_pedido (pedido_id, producto_id, precio_unitario, cantidad, es_recompensa, bistrocoins_unitarios)
             VALUES (?, ?, ?, 1, ?, ?)
             ON DUPLICATE KEY UPDATE cantidad = cantidad + 1"
        );
        $stmt->bind_param("iidii", $pedido_id, $producto_id, $precio_unitario, $esRecompensa, $bistrocoinsUnitarios);
        return $stmt->execute();
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
            (int)$recompensa->getProductoId(),
            0.0,
            1,
            (int)$recompensa->getBistrocoins()
        );

        return [$ok, $ok ? 'Recompensa añadida al pedido.' : 'No se pudo añadir la recompensa al pedido.'];
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    public static function updateCantidad($pedido_id, $producto_id, $cantidad)
    {
        global $conn;
        $stmt = $conn->prepare(
            "UPDATE productos_en_pedido SET cantidad = ?
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

    /**
     * Elimina un producto del carrito.
     */
    public static function removeProducto($pedido_id, $producto_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "DELETE FROM productos_en_pedido WHERE pedido_id = ? AND producto_id = ? AND es_recompensa = 0"
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
    /**
     * Cancela y elimina el pedido y sus líneas.
     */
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

    /**
     * Confirma el pedido: asigna numero_pedido del día, cambia estado a 'recibido' y guarda el total.
     */
    public static function confirmarPedido($pedido_id, $metodo_pago, $total)
    {
        global $conn;

        // Calcular el siguiente número de pedido del día
        $stmtNumero = $conn->prepare("
            SELECT COALESCE(MAX(numero_pedido), 0) + 1 AS siguiente 
            FROM pedidos 
            WHERE DATE(fecha_hora) = CURDATE() AND estado != 'nuevo'
        ");
        $stmtNumero->execute();
        $rs = $stmtNumero->get_result();
        $numero = $rs->fetch_assoc()['siguiente'];
        $rs->free();
        $stmtNumero->close();


        $estado = ($metodo_pago === 'tarjeta') ? 'en_preparacion' : 'recibido';

        // Si el pedido no contiene ningún producto que se cocine, lo saltamos directamente
        $stmtCheck = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM productos_en_pedido pep JOIN productos p ON p.id = pep.producto_id WHERE pep.pedido_id = ? AND p.se_cocina = 1"
        );
        $stmtCheck->bind_param("i", $pedido_id);
        $stmtCheck->execute();
        $rsCheck = $stmtCheck->get_result();
        $rowCheck = $rsCheck->fetch_assoc();
        $stmtCheck->close();

        $terminarPedido = (($rowCheck['cnt'] ?? 0) == 0);

        if ($terminarPedido) {
            // No hay nada que cocinar → el pedido queda listo para entregar
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

        if ($ok && $terminarPedido) {
            self::terminarPedidoParaEntrega($pedido_id);
        }

        if ($ok && $metodo_pago === 'tarjeta') {
            self::liquidarBistroCoinsSiProcede($pedido_id);
        }

        return $ok;
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


    public static function getPedidosDeUsuario($usuario_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "SELECT * FROM pedidos
         WHERE usuario_id = ? AND estado != 'nuevo'
         ORDER BY fecha_hora DESC"
        );
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $pedidos = [];

        while ($fila = $result->fetch_assoc()) {
            $pedidos[] = new Pedido(
            $fila['id'],
            $fila['numero_pedido'],
            $fila['fecha_hora'],
            $fila['fecha'],
            $fila['estado'],
            $fila['tipo'],
            $fila['metodo_pago'],
            $fila['usuario_id'],
            $fila['total_sin_descuentos'],
            $fila['total_descuento'],
            $fila['cocinero_id'],
            $fila['total'] ?? null
            );
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

        if ($ok && $estado_nuevo === 'en_preparacion') {
            $ok = self::liquidarBistroCoinsSiProcede($pedido_id);
        }

        return $ok;
    }

    /**
     * Devuelve los productos de un pedido con nombre, imagen, iva y estado.
     */
    public static function getProductosPedido($pedido_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "SELECT pep.*, p.nombre, p.imagen
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

    /**
     * Devuelve los pedidos por estado con los datos del cliente.
     */
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
        // Cambia el estado de una sola línea del pedido a 'preparado'
        $stmt = $conn->prepare("UPDATE productos_en_pedido SET estado = 'preparado' WHERE pedido_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $pedido_id, $producto_id);
        return $stmt->execute();
    }

    public static function marcarProductoPreparadoCamarero($pedido_id, $producto_id)
    {
        $ok = self::marcarProductoPreparado($pedido_id, $producto_id);

        if ($ok && self::todosProductosBarraPreparados($pedido_id)) {
            self::terminarPedidoParaEntrega($pedido_id);
        }

        return $ok;
    }

    private static function todosProductosBarraPreparados($pedido_id): bool
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

        return ((int)($row['pendientes'] ?? 0)) === 0;
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




    // --- ESTADOS GLOBALES DEL PEDIDO (FUNCIONALIDAD 3) ---

    public static function getPedidosCocinando($cocinero_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM pedidos WHERE cocinero_id = ? AND estado = 'cocinando' ORDER BY fecha_hora ASC");
        $stmt->bind_param("i", $cocinero_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

    // --- PANEL DE GERENTE (FUNCIONALIDAD 3) ---
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

    /**
     * Obtiene los pedidos activos de un usuario para su perfil 
     */
    public static function getPedidosActivosByUsuario(int $usuario_id): array
    {
        global $conn;
        // Editamos la query para calcular el 'total' usando las columnas existentes
        $sql = "
            SELECT numero_pedido, estado, fecha_hora, total
            FROM pedidos
            WHERE usuario_id = ?
               AND estado IN ('en_preparacion', 'cocinando', 'listo_cocina', 'terminado')
            ORDER BY fecha_hora DESC
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $row['lineas'] = self::getResumenLineasPedido((int)$row['id']);
            $pedidos[] = $row;
        }

        $result->free();

        $stmt->close();
        return $pedidos;
    }

    /**
     * Obtiene el histórico de pedidos de un usuario para su perfil
     */
    public static function getPedidosHistoricoByUsuario(int $usuario_id): array
    {
        global $conn;
        $sql = "
            SELECT numero_pedido, fecha_hora, tipo, total, estado, bistrocoins_generados, bistrocoins_gastados
            FROM pedidos
            WHERE usuario_id = ? AND estado != 'nuevo'
            ORDER BY fecha_hora DESC
            LIMIT 15
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $row['lineas'] = self::getResumenLineasPedido((int)$row['id']);
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
        $stmt->bind_param('i', $pedido_id);
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
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE pedidos 
             SET total_sin_descuentos = ?, total_descuento = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ddi", $total_sin_descuentos, $total_descuento, $pedido_id);
        $stmt->execute();
        $stmt->close();
    }

    public static function limpiarOfertas($pedido_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "DELETE FROM ofertas_en_pedido WHERE pedido_id = ?"
        );

        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();
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
            $ofertas[] = (object)[
                'id' => $fila['id'],
                'pedido_id' => $fila['pedido_id'],
                'oferta_id' => $fila['oferta_id'],
                'nombre' => $fila['nombre'],
                'veces_aplicada' => $fila['veces_aplicada'],
                'descuento_total' => $fila['descuento_total']
            ];
        }

        $stmt->close();

        return $ofertas;
    }

    /**
     * Cuenta los pedidos activos de un usuario para no cargar los datos completos en el perfil solo para ver el número
     */
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

        return (int)($row['num_activos'] ?? 0);
    }

    public static function getBistrocoinsGastadosPedido(int $pedido_id): int
    {
        global $conn;
        $stmt = $conn->prepare("SELECT COALESCE(SUM(cantidad * bistrocoins_unitarios), 0) AS total FROM productos_en_pedido WHERE pedido_id = ? AND es_recompensa = 1");
        $stmt->bind_param('i', $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        $stmt->close();
        return (int)($row['total'] ?? 0);
    }

    public static function liquidarBistroCoinsSiProcede(int $pedido_id): bool
    {
        global $conn;
        $stmt = $conn->prepare("SELECT usuario_id, total, bistrocoins_liquidados FROM pedidos WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $result->free();
        $stmt->close();

        if (!$pedido) {
            return false;
        }
        if ((int)$pedido['bistrocoins_liquidados'] === 1) {
            return true;
        }

        $usuarioId = (int)$pedido['usuario_id'];
        $gastados = self::getBistrocoinsGastadosPedido($pedido_id);
        $generados = (int)floor(max(0, (float)$pedido['total']));
        $saldo = UsuarioDAO::getBistrocoinsByUserId($usuarioId);

        if ($saldo < $gastados) {
            return false;
        }

        $nuevoSaldo = $saldo - $gastados + $generados;

        $conn->begin_transaction();
        try {
            $stmtUser = $conn->prepare("UPDATE usuarios SET bistrocoins = ?, updated_at = NOW() WHERE id = ?");
            $stmtUser->bind_param('ii', $nuevoSaldo, $usuarioId);
            $stmtUser->execute();
            $stmtUser->close();

            $stmtPedido = $conn->prepare("UPDATE pedidos SET bistrocoins_generados = ?, bistrocoins_gastados = ?, bistrocoins_liquidados = 1 WHERE id = ?");
            $stmtPedido->bind_param('iii', $generados, $gastados, $pedido_id);
            $stmtPedido->execute();
            $stmtPedido->close();

            $conn->commit();
            return true;
        } catch (\Throwable $e) {
            $conn->rollback();
            return false;
        }
    }
}
