<?php
require_once __DIR__ . '/../entities/categoria.php';
require_once __DIR__ . '/application.php';

class CategoriaDAO {

    public static function getAll() {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM categorias");
        $stmt->execute();

        $result = $stmt->get_result();

        $categorias = [];

        while ($row = $result->fetch_assoc()) {
            $categorias[] = new Categoria(
                $row['id'],
                $row['nombre'],
                $row['descripcion'],
                $row['imagen'],
                $row['activa']
            );
        }

        $result->free();
        $stmt->close();

        return $categorias;
    }

    public static function getById($id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) return null;

        return new Categoria(
            $row['id'],
            $row['nombre'],
            $row['descripcion'],
            $row['imagen'],
            $row['activa']
        );
    }

    public static function create($nombre, $descripcion, $imagen) {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, imagen, activa) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $nombre, $descripcion, $imagen);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function update($id, $nombre, $descripcion, $imagen) {
        global $conn;

        $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $descripcion, $imagen, $id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function desactivar($id) {
        global $conn;

        $conn->begin_transaction();

        try {
            // Desactivar categoría
            $stmt = $conn->prepare("UPDATE categorias SET activa = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Desactivar productos de la categoría
            $stmt2 = $conn->prepare("UPDATE productos SET ofertado = 0 WHERE categoria_id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $stmt2->close();

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public static function activar($id) {
        global $conn;

        $conn->begin_transaction();

        try {
            // Activar categoría
            $stmt = $conn->prepare("UPDATE categorias SET activa = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Activar productos de la categoría
            $stmt2 = $conn->prepare("UPDATE productos SET ofertado = 1 WHERE categoria_id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $stmt2->close();

            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }
}