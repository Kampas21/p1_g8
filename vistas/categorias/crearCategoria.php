<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCategoria.php';

use es\ucm\fdi\aw\Formulario\FormularioCategoria;

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    http_response_code(403);
    die('Acceso denegado');
}

$form = new FormularioCategoria();
$htmlForm = $form->gestiona();

$tituloPagina = 'Crear categoría';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>
<div class="panel">
    <h1>Nueva categoría</h1>
    <?= $htmlForm ?>
    <p><a class="btn-volver" href="categoriasList.php">← Volver</a></p>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';