<?php
require_once __DIR__ . '/../../entities/categoria.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID de categoría no válido.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre !== '' && $descripcion !== '') {
        Categoria::editarCategoria((int)$id, $nombre, $descripcion);
        header('Location: categoriasList.php');
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}

$categoria = Categoria::getCategoriaById((int)$id);

if (!$categoria) {
    die("La categoría no existe.");
}

require __DIR__ . '/categoriasForm.php';