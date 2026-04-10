<?php



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

    $nombre = trim((string) filter_input(INPUT_POST, 'nombre'));
    $descripcion = trim((string) filter_input(INPUT_POST, 'descripcion'));

    if (!$nombre || strlen($nombre) < 3) {
        $errores[] = "El nombre debe tener al menos 3 caracteres.";
    }

    if (!$descripcion) {
        $errores[] = "La descripción es obligatoria.";
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

    <ul class="errores">
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