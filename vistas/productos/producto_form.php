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

$modo = (string)($_GET['modo'] ?? '');
$id = (int)($_GET['id'] ?? 0);
$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die("Categoría inválida.");
}

$isCreate = ($modo === 'crear') || ($id <= 0);
$productoToEdit = null;

if (!$isCreate) {
    $productoToEdit = \ProductoDAO::getById($id);
    if (!$productoToEdit) {
        header("Location: mostrarProductosCategoria.php?id=" . $categoria_id);
    }
}

$form = new FormularioProducto($isCreate, $categoria_id, $productoToEdit);
$htmlFormProducto = $form->gestiona();

$tituloAccion = $isCreate ? 'Nuevo Producto' : 'Editar Producto';
$tituloPagina = $tituloAccion;
ob_start();
?>

<div class="panel">
    <h2><?= htmlspecialchars($tituloAccion) ?></h2>
    
    <?= $htmlFormProducto ?>
    
    <div class="mt-20">
        <a class="btn" href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">&laquo; Cancelar</a>
    </div>
</div>

<?php 
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php'; 
?>