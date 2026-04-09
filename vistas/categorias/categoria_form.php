<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$modo = $id ? 'editar' : 'crear';

$categoria = null;
$errores = [];

if ($modo === 'editar') {
    $categoria = CategoriaService::getById($id);

    if (!$categoria) {
        die("Categoría no encontrada");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (!$nombre || strlen($nombre) < 3) {
        $errores[] = "Nombre inválido";
    }

    if (!$descripcion) {
        $errores[] = "Descripción obligatoria";
    }

    if (empty($errores)) {

        if ($modo === 'crear') {
            CategoriaService::create($nombre, $descripcion);
        } else {
            CategoriaService::update($id, $nombre, $descripcion);
        }

        header("Location: categoriasList.php");
        exit;
    }
}

$nombreValue = $categoria ? $categoria->getNombre() : '';
$descripcionValue = $categoria ? $categoria->getDescripcion() : '';

$tituloPagina = 'Categoría';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1><?= $modo === 'crear' ? 'Crear' : 'Editar' ?> categoría</h1>

<?php if (!empty($errores)): ?>
    <ul style="color:red;">
        <?php foreach ($errores as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required minlength="3"
           value="<?= htmlspecialchars($nombreValue) ?>"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" required><?= htmlspecialchars($descripcionValue) ?></textarea><br><br>

    <button type="submit">Guardar</button>

</form>

<br>
<a href="categoriasList.php">← Volver</a>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';