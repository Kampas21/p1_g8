<?php
require_once __DIR__ . '/../includes/application.php';

class Categoria
{

    public static function getCategorias()
    {
        global $conn;

        $query = "SELECT * FROM categorias";
        $rs = $conn->query($query);

        $categorias = [];

        while ($fila = $rs->fetch_assoc()) {
            $categorias[] = $fila;
        }

        return $categorias;
    }

    public static function crearCategoria($nombre, $descripcion)
    {
        global $conn;

        $stmt = $conn->prepare(
            "INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)"
        );

        $stmt->bind_param("ss", $nombre, $descripcion);
        $stmt->execute();
    }

    public static function borrarCategoria($id)
    {
        global $conn;
        
        $stmt = $conn->prepare(
            "DELETE FROM categorias WHERE id=?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}
