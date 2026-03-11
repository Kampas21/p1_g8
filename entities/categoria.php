<?php 
require_once __DIR__ . '/../includes/application.php';
require_once __DIR__ . '/producto.php';

class Categoria
{
    public static function getCategorias($soloActivas = false)
    {
        global $conn;

        $query = "SELECT * FROM categorias";
        if ($soloActivas) {
            $query .= " WHERE activa = 1";
        }

        $rs = $conn->query($query);

        $categorias = [];

        while ($fila = $rs->fetch_assoc()) {
            $categorias[] = $fila;
        }

        return $categorias;
    }

    public static function getCategoriaById($id)
    {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public static function crearCategoria($nombre, $descripcion)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO categorias (nombre, descripcion, activa) VALUES (?, ?, 1)"
        );

        $stmt->bind_param("ss", $nombre, $descripcion);
        return $stmt->execute();
    }

    public static function editarCategoria($id, $nombre, $descripcion)
    {
        global $conn;

        $stmt = $conn->prepare(
            "UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?"
        );

        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        return $stmt->execute();
    }

    public static function borrarCategoria($id)
    {
        global $conn;

        $conn->begin_transaction();

        try {
            Producto::desactivarProductosPorCategoria($id);

            $stmt = $conn->prepare(
                "UPDATE categorias SET activa = 0 WHERE id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $conn->commit();
            return true;
        } catch (Throwable $e) {
            $conn->rollback();
            return false;
        }
    }
}