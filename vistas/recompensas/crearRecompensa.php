<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioRecompensa.php';
use es\ucm\fdi\aw\Formulario\FormularioRecompensa;
require_role('gerente');
$form = new FormularioRecompensa();
$htmlForm = $form->gestiona();
$tituloPagina = 'Nueva recompensa | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>
<div class="panel">
  <h2>Nueva recompensa</h2>
  <?= $htmlForm ?>
  <p><a class="btn-volver" href="listarRecompensas.php">← Volver</a></p>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
