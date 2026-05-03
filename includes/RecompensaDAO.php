<?php
require_once __DIR__ . '/../entities/Recompensa.php';
require_once __DIR__ . '/application.php';

class RecompensaDAO {
    private static function rowToRecompensa(array $fila): Recompensa {
        return new Recompensa(
            $fila['id'],
            $fila['producto_id'],
            $fila['bistrocoins'],
            $fila['activa'] ?? 1,
            $fila['producto_nombre'] ?? '',
            $fila['producto_descripcion'] ?? '',
            $fila['producto_precio_final'] ?? 0,
            $fila['producto_imagen'] ?? null
        );
    }

    public static function getAll(bool $includeInactive = true): array {
        global $conn;
        $sql = "SELECT r.*, p.nombre AS producto_nombre, p.descripcion AS producto_descripcion,
                       ROUND(p.precio_base * (1 + p.iva / 100), 2) AS producto_precio_final,
                       p.imagen AS producto_imagen
                FROM recompensas r
                JOIN productos p ON p.id = r.producto_id";
        if (!$includeInactive) {
            $sql .= " WHERE r.activa = 1";
        }
        $sql .= " ORDER BY r.activa DESC, r.bistrocoins ASC, p.nombre ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $recompensas = [];
        while ($fila = $result->fetch_assoc()) {
            $recompensas[] = self::rowToRecompensa($fila);
        }
        $result->free();
        $stmt->close();
        return $recompensas;
    }

    public static function getById(int $id): ?Recompensa {
        global $conn;
        $sql = "SELECT r.*, p.nombre AS producto_nombre, p.descripcion AS producto_descripcion,
                       ROUND(p.precio_base * (1 + p.iva / 100), 2) AS producto_precio_final,
                       p.imagen AS producto_imagen
                FROM recompensas r
                JOIN productos p ON p.id = r.producto_id
                WHERE r.id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();
        $result->free();
        $stmt->close();
        return $fila ? self::rowToRecompensa($fila) : null;
    }

    public static function create(int $productoId, int $bistrocoins): bool {
        global $conn;
        $activa = 1;
        $stmt = $conn->prepare("INSERT INTO recompensas (producto_id, bistrocoins, activa) VALUES (?, ?, ?)");
        $stmt->bind_param('iii', $productoId, $bistrocoins, $activa);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function update(int $id, int $productoId, int $bistrocoins, int $activa = 1): bool {
        global $conn;
        $stmt = $conn->prepare("UPDATE recompensas SET producto_id = ?, bistrocoins = ?, activa = ? WHERE id = ?");
        $stmt->bind_param('iiii', $productoId, $bistrocoins, $activa, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function delete(int $id): bool {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM recompensas WHERE id = ?");
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public static function existsForProducto(int $productoId, ?int $excludeId = null): bool {
        global $conn;
        if ($excludeId) {
            $stmt = $conn->prepare("SELECT id FROM recompensas WHERE producto_id = ? AND id != ? LIMIT 1");
            $stmt->bind_param('ii', $productoId, $excludeId);
        } else {
            $stmt = $conn->prepare("SELECT id FROM recompensas WHERE producto_id = ? LIMIT 1");
            $stmt->bind_param('i', $productoId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = (bool)$result->fetch_assoc();
        $result->free();
        $stmt->close();
        return $exists;
    }
}
