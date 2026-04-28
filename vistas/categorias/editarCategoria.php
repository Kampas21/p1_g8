<?php


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/CategoriaDAO.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCategoria.php';

use es\ucm\fdi\aw\Formulario\FormularioCategoria;

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    http_response_code(403);
    die('Acceso denegado');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    die('ID inválido');
}

$categoria = CategoriaDAO::getById($id);
if (!$categoria) {
    http_response_code(404);
    die('Categoría no encontrada');
}

$form = new FormularioCategoria($categoria);
$htmlForm = $form->gestiona();

$tituloPagina = 'Editar categoría';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>
<div class="panel">
    <h1>Editar categoría</h1>
    <?= $htmlForm ?>
    <p><a class="btn-volver" href="categoriasList.php">← Volver</a></p>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';