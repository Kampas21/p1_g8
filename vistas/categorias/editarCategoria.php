<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCategoria.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

use es\ucm\fdi\aw\Formulario\FormularioCategoria;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("ID inválido");
}

$categoria = CategoriaService::getById($id);

if (!$categoria) {
    die("Categoría no encontrada");
}

$form = new FormularioCategoria($categoria);

$htmlForm = $form->gestiona();

$tituloPagina = "Editar Categoría";

require __DIR__ . '/../plantillas/plantilla.php';