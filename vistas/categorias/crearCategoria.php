<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCategoria.php';

use es\ucm\fdi\aw\Formulario\FormularioCategoria;

$form = new FormularioCategoria();

$htmlForm = $form->gestiona();

$tituloPagina = "Crear Categoría";

require __DIR__ . '/../plantillas/plantilla.php';