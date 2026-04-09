<?php
require_once __DIR__ . '/../entities/Oferta.php';
require_once __DIR__ . '/../includes/application.php';
class OfertaService
{
    // Obtener todas las ofertas
    public static function getAll()
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM ofertas ORDER BY fecha_inicio DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        $ofertas = [];
        while ($fila = $result->fetch_assoc()) {
            $ofertas[] = new Oferta(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['fecha_inicio'],
                $fila['fecha_fin'],
                $fila['descuento']
            );
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    // Obtener solo ofertas activas
    public static function getAllActivas()
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT * FROM ofertas 
             WHERE NOW() BETWEEN fecha_inicio AND fecha_fin 
             AND activa = 1"
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $ofertas = [];
        while ($fila = $result->fetch_assoc()) {
            $ofertas[] = new Oferta(
                $fila['id'],
                $fila['nombre'],
                $fila['descripcion'],
                $fila['fecha_inicio'],
                $fila['fecha_fin'],
                $fila['descuento']
            );
        }

        $result->free();
        $stmt->close();

        return $ofertas;
    }

    // Obtener oferta por ID
    public static function getById($id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $fila = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$fila) {
            return null;
        }

        return new Oferta(
            $fila['id'],
            $fila['nombre'],
            $fila['descripcion'],
            $fila['fecha_inicio'],
            $fila['fecha_fin'],
            $fila['descuento']
        );
    }

    // Crear nueva oferta
    public static function crearOferta($nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO ofertas (nombre, descripcion, fecha_inicio, fecha_fin, descuento)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssssd", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento);
        $stmt->execute();

        $insertId = $conn->insert_id;
        $stmt->close();

        return $insertId;
    }

    // Editar oferta existente
    public static function editarOferta($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE ofertas 
             SET nombre = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ?, descuento = ?
             WHERE id = ?"
        );

        $stmt->bind_param("ssssdi", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $descuento, $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    // Borrar oferta
    public static function borrarOferta($id)
    {
        global $conn;

        $stmt = $conn->prepare("DELETE FROM ofertas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    // Ver si la oferta está en uso
    public static function ofertaEnUso($oferta_id)
    {
        global $conn;

        $stmt = $conn->prepare(
            "SELECT pedido_id
             FROM ofertas_en_pedido
             WHERE oferta_id = ?"
        );

        $stmt->bind_param("i", $oferta_id);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $resultado;
    }
}

