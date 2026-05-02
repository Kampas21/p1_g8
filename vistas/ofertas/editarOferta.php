<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioOferta.php';

use es\ucm\fdi\aw\Formulario\FormularioOferta;

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

$oferta = OfertaDAO::getById($id);
if (!$oferta) {
    http_response_code(404);
    die('Oferta no encontrada');
}

$form = new FormularioOferta($oferta);
$htmlForm = $form->gestiona();

$tituloPagina = 'Editar oferta';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>
<div class="panel">
    <h1>Editar oferta</h1>
    <?= $htmlForm ?>
    <p><a class="btn-volver" href="listarOfertas.php">← Volver</a></p>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';