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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre !== '' && $descripcion !== '') {
        Categoria::editarCategoria((int)$id, $nombre, $descripcion);
        header('Location: categoriasList.php');
        exit();
    }

    $error = "Todos los campos son obligatorios.";
}

$categoria = Categoria::getCategoriaById((int)$id);

if (!$categoria) {
    die("La categoría no existe.");
}

require __DIR__ . '/categoriasForm.php';