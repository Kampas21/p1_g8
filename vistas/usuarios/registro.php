<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioRegistro.php';

use es\ucm\fdi\aw\Formulario\FormularioRegistro;

$currentUser = current_user();
if ($currentUser) {
    if ($currentUser['rol'] === 'gerente') {
        header("Location: ".RUTA_APP."/vistas/usuarios/usuario_form.php?modo=crear");
    } else {
        header("Location: ".RUTA_APP."/vistas/usuarios/perfil.php");
    }
    exit();
}

$form = new FormularioRegistro();
$htmlFormRegistro = $form->gestiona();

$tituloPagina = 'Registro | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2>Registro de nuevos clientes</h2>
    <?= $htmlFormRegistro ?>
</div>

<?php 
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php'; 
?>