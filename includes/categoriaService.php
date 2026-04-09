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

<<<<<<< HEAD
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $result->free();
        $stmt->close();

        if (!$row) {
            return null;
        }
=======
        $row = $stmt->get_result()->fetch_assoc();
>>>>>>> 02a7ae2da73ba46f74f12d6b3ce5d1d78125ca97

        return new Categoria(
            $row['id'],
            $row['nombre'],
            $row['descripcion'],
<<<<<<< HEAD
            $row['activa']
        );
    }

    public static function create($nombre, $descripcion) {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion, activa) VALUES (?, ?, 1)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        $stmt->execute();
        $stmt->close();
    }

    public static function update($id, $nombre, $descripcion) {
        global $conn;

        $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        $stmt->execute();
        $stmt->close();
    }

    public static function desactivar($id) {
        global $conn;

        $stmt = $conn->prepare("UPDATE categorias SET activa = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $stmt2 = $conn->prepare("UPDATE productos SET ofertado = 0 WHERE categoria_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
    }

    public static function activar($id) {
        global $conn;

        $stmt = $conn->prepare("UPDATE categorias SET activa = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $stmt2 = $conn->prepare("UPDATE productos SET ofertado = 1 WHERE categoria_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
    }
=======
            $row['imagen'],
            $row['activa']
        );
    }
>>>>>>> 02a7ae2da73ba46f74f12d6b3ce5d1d78125ca97
}