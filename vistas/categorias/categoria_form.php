<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../entities/categoria.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioCategoria.php';

use es\ucm\fdi\aw\Formulario\FormularioCategoria;

$user = require_role('gerente');

$modo = (string)($_GET['modo'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$isCreate = ($modo === 'crear') || ($id <= 0);
$categoriaToEdit = null;

if (!$isCreate) {
    // Obtenemos la categoría usando la función estática de tu compañero
    $categoriaToEdit = \Categoria::getCategoriaById($id);
    if (!$categoriaToEdit) {
        header("Location: categoriasList.php");
        exit();
    }
}

$form = new FormularioCategoria($isCreate, $categoriaToEdit);
$htmlFormCategoria = $form->gestiona();

$tituloAccion = $isCreate ? 'Nueva Categoría' : 'Editar Categoría';
$tituloPagina = $tituloAccion . ' | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2><?= htmlspecialchars($tituloAccion) ?></h2>
    
    <?= $htmlFormCategoria ?>
    
    <div style="margin-top:20px;">
        <a class="btn" href="categoriasList.php">&laquo; Cancelar y volver al listado</a>
    </div>
</div>

<?php 
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php'; 
?>