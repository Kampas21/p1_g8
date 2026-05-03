<?php
declare(strict_types=1);


require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioLogin.php';

use es\ucm\fdi\aw\Formulario\FormularioLogin; 

if (current_user()) {
    header("Location: ".RUTA_APP."/vistas/usuarios/perfil.php");
    exit();
}

// Instanciamos la clase del formulario login
$form = new FormularioLogin();
// gestiona si hay POST o no y llama a procesaFormulario o generaCamposFormulario
$htmlFormLogin = $form->gestiona();

$tituloPagina = 'Iniciar sesión | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2>Iniciar sesión</h2>
    
    <?php foreach (flash_get_all() as $f): ?>
        <div class="mensaje-<?= escaparHtml($f['type']) ?>"><?= escaparHtml($f['message']) ?></div>
    <?php endforeach; ?>
    
    <!-- HTML procesado por el objeto -->
    <?= $htmlFormLogin ?>
    
</div>

<?php 
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php'; 
?>