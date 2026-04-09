<?php
require_once __DIR__ . '/../entities/Categoria.php';
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

        return $categorias;
    }

    public static function getById($id) {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return new Categoria(
            $row['id'],
            $row['nombre'],
            $row['descripcion'],
            $row['imagen'],
            $row['activa']
        );
    }
}