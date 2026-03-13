<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
    ?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Necesitas ser gerente para realizar esta acción.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

require_once __DIR__ . '/../../entities/categoria.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID de categoría no válido.");
}

$categoria = Categoria::getCategoriaById((int)$id);

if (!$categoria) {
    die("La categoría no existe.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Categoria::borrarCategoria((int)$id);
    header('Location: categoriasList.php');
    exit();
}

$tituloPagina = 'Borrar Categorías';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<h1>Desactivar categoría</h1>

<p>
    ¿Seguro que quieres desactivar la categoría
    <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>?
</p>

<p>
    Sus productos pasarán a no ofertados.
</p>

<form method="POST">
    <p><button type="submit" class="btn-aceptar">Sí, desactivar</button></p>
</form>

<p>
    <a class="btn-volver" href="categoriasList.php">Cancelar</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';