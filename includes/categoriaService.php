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
}