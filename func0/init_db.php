<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

$admin = current_user();
$allowReset = isset($_GET['reset']) && ($_GET['reset'] === '1');

if ($allowReset) {
    // Por seguridad, solo gerente autenticado puede resetear vía web.
    if (!$admin || !user_has_role($admin, 'gerente')) {
        http_response_code(403);
        echo 'Solo un gerente autenticado puede resetear la BD.';
        exit;
    }
    $dbFile = db_path();
    if (is_file($dbFile)) {
        @unlink($dbFile);
    }
    ensure_database_ready();
    flash_set('success', 'Base de datos reinicializada con datos de prueba.');
    redirect('index.php');
}

layout_header('Inicialización BD');
?>
<main>
  <div class="panel">
    <h2>Base de datos inicializada</h2>
    <?php layout_flash_messages(); ?>
    <p>La BD SQLite está lista en <code>prototipo/data/bistro_fdi.sqlite</code>.</p>
    <p>Si necesitas resetearla, inicia sesión como gerente y accede a:
      <code>init_db.php?reset=1</code>.
    </p>
    <p><a class="btn" href="index.php">Volver</a></p>
  </div>
</main>
