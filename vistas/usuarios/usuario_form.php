<?php
declare(strict_types=1);



require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioAdminUsuario.php';

use es\ucm\fdi\aw\Formulario\FormularioAdminUsuario;

$admin = require_role('gerente');

$modo = (string)($_GET['modo'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$isCreate = ($modo === 'crear') || ($id <= 0);
$userToEdit = null;

if (!$isCreate) {
    $userToEdit = user_find_by_id($id);
    if (!$userToEdit) {
        flash_set('error', 'El usuario especificado no existe.');
        redirect('usuarios.php');
    }
}

// Instanciamos nuestro objeto formulario
$form = new FormularioAdminUsuario($isCreate, $userToEdit);
$htmlFormUsuario = $form->gestiona();

$tituloAccion = $isCreate ? 'Crear Nuevo Usuario' : 'Editar Usuario';
$tituloPagina = $tituloAccion . ' | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2><?= e($tituloAccion) ?></h2>
    
    <?php foreach (flash_get_all() as $f): ?>
        <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
    
    <!-- Imprimimos el formulario generado por el Objeto -->
    <?= $htmlFormUsuario ?>
    
    <div class="mt-20">
        <?php if ($isCreate): ?>
            <a class="btn" href="usuarios.php">&laquo; Volver al listado</a>
        <?php else: ?>
            <a class="btn" href="usuario_ver.php?id=<?= $id ?>">&laquo; Volver al perfil</a>
        <?php endif; ?>
    </div>
</div>

<?php 
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php'; 
?>
