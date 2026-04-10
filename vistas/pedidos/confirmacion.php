<?php
session_start();

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';

$user = require_login();

$tituloPagina = 'Pedido confirmado | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<main>
  <div class="panel">
    <h2>Pedido confirmado</h2>
    <p>Tu pedido se ha registrado correctamente.</p>
    <p>Puedes consultar su estado desde tu perfil o seguir navegando por la aplicación.</p>

    <div class="actions-inline mt-16">
      <a href="<?= RUTA_APP ?>/index.php" class="btn">Volver al inicio</a>
      <a href="<?= RUTA_APP ?>/vistas/usuarios/perfil.php" class="btn primary">Ver mi perfil</a>
    </div>
  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>