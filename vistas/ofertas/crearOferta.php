<?php 

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioOferta.php';

use es\ucm\fdi\aw\Formulario\FormularioOferta;


$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    http_response_code(403);
    die('Acceso denegado');
}

$form = new FormularioOferta();
$htmlForm = $form->gestiona();

$tituloPagina = 'Crear oferta';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>
<div class="panel">
    <h1>Nueva oferta</h1>
    <?= $htmlForm ?>
    <p><a class="btn-volver" href="listarOfertas.php">← Volver</a></p>
</div>

<!-- <script src="../../JS/oferta.js"></script> -->

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';