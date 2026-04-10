<?php
declare(strict_types=1);


require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioProducto.php';

use es\ucm\fdi\aw\Formulario\FormularioProducto;

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    header('Location: ../../index.php');
    exit();
}

$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);
if (!$categoria_id) {
    http_response_code(400);
    die('Categoría inválida.');
}

$form = new FormularioProducto(true, $categoria_id, null);
$htmlFormProducto = $form->gestiona();

$tituloPagina = 'Nuevo Producto';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>
<div class="panel">
    <h2>Nuevo Producto</h2>
    <?= $htmlFormProducto ?>
    <div class="mt-20">
        <a class="btn" href="mostrarProductosCategoria.php?id=<?= (int)$categoria_id ?>">&laquo; Cancelar</a>
    </div>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';