<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/RecompensaDAO.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioRecompensa.php';
use es\ucm\fdi\aw\Formulario\FormularioRecompensa;
require_role('gerente');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); die('ID inválido'); }
$recompensa = RecompensaDAO::getById($id);
if (!$recompensa) { http_response_code(404); die('Recompensa no encontrada'); }
$form = new FormularioRecompensa($recompensa);
$htmlForm = $form->gestiona();
$tituloPagina = 'Editar recompensa | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>
<div class="panel">
  <h2>Editar recompensa</h2>
  <?= $htmlForm ?>
  <p><a class="btn-volver" href="listarRecompensas.php">← Volver</a></p>
</div>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
