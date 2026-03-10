<?php
require_once __DIR__ . '/../../entities/categoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre !== '' && $descripcion !== '') {
        Categoria::crearCategoria($nombre, $descripcion);
        header('Location: categoriasList.php');
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}

require __DIR__ . '/categoriasForm.php';