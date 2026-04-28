<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioProducto.php';

use es\ucm\fdi\aw\Formulario\FormularioProducto;

$user = current_user();
if (!$user || !user_has_role($user, 'gerente')) {
    header('Location: ../../index.php');
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

// Si nos pasan ID, estamos en modo Editar; de lo contrario, en modo Crear.
$esCreacion = !$id;
$productoToEdit = null;

if (!$categoria_id && !$id) {
    http_response_code(400);
    die('Parámetros inválidos.');
}

if (!$esCreacion) {
    $productoToEdit = ProductoDAO::getById($id);
    if (!$productoToEdit) {
        http_response_code(404);
        die('Producto no encontrado.');
    }
    if (!$categoria_id) {
        $categoria_id = $productoToEdit->getCategoriaId();
    }
}

$form = new FormularioProducto($esCreacion, $categoria_id, $productoToEdit);
$htmlFormProducto = $form->gestiona();

$tituloAccion = $esCreacion ? 'Nuevo Producto' : 'Editar Producto';
$tituloPagina = $tituloAccion;
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>
<div class="panel">
    <h2><?= htmlspecialchars($tituloAccion) ?></h2>
    <?= $htmlFormProducto ?>
    <div class="mt-20">
        <a class="btn" href="mostrarProductosCategoria.php?id=<?= (int)$categoria_id ?>">&laquo; Cancelar</a>
    </div>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';