<?php
require_once __DIR__ . '/../entities/categoria.php';
require_once __DIR__ . '/application.php';

class CategoriaService {

    public static function getAll() {
        global $conn;

        $result = $conn->query("SELECT * FROM categorias");

        $categorias = [];

        while ($row = $result->fetch_assoc()) {
            $categorias[] = new Categoria(
                $row['id'],
                $row['nombre'],
                $row['descripcion'],
                $row['activa']
            );
        }

        $result->free();

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

        if (!$row) {
            return null;
        }

        return new Categoria(
            $row['id'],
            $row['nombre'],
            $row['descripcion'],
            $row['activa']
        );
    }

    public static function create($nombre, $descripcion) {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, activa) VALUES (?, ?, 1)");
        $stmt->bind_param("ss", $nombre, $descripcion);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function update($id, $nombre, $descripcion) {
        global $conn;

        $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);

        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public static function desactivar($id) {
        global $conn;

        // 🔹 Desactivar categoría
        $stmt = $conn->prepare("UPDATE categorias SET activa = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok1 = $stmt->execute();
        $stmt->close();

        // 🔹 Desactivar productos de esa categoría
        $stmt2 = $conn->prepare("UPDATE productos SET ofertado = 0 WHERE categoria_id = ?");
        $stmt2->bind_param("i", $id);
        $ok2 = $stmt2->execute();
        $stmt2->close();

        return $ok1 && $ok2;
    }

    public static function activar($id) {
        global $conn;

        // 🔹 Activar categoría
        $stmt = $conn->prepare("UPDATE categorias SET activa = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok1 = $stmt->execute();
        $stmt->close();

        // 🔹 Activar productos de esa categoría
        $stmt2 = $conn->prepare("UPDATE productos SET ofertado = 1 WHERE categoria_id = ?");
        $stmt2->bind_param("i", $id);
        $ok2 = $stmt2->execute();
        $stmt2->close();

        return $ok1 && $ok2;
    }
}