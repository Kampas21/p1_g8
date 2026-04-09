<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
    ?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>No tienes permisos.</p>
        <a href="../../index.php">Volver</a>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}


$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die("Categoría inválida");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $iva = $_POST['iva'] ?? 0;

    ProductoService::create($nombre, $descripcion, $categoria_id, $precio, $iva);

    header("Location: mostrarProductosCategoria.php?id=" . $categoria_id);
    exit;
}


$tituloPagina = 'Crear producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Crear producto</h1>

<form method="POST">

    <label>Nombre</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Descripción</label><br>
    <textarea name="descripcion" required></textarea><br><br>

    <label>Precio</label><br>
    <input type="number" step="0.01" name="precio" required><br><br>

    <label>IVA (%)</label><br>
    <select name="iva">
        <option value="4">4%</option>
        <option value="10">10%</option>
        <option value="21">21%</option>
    </select><br><br>

    <button type="submit">Crear</button>
</form>

<p>
    <a href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">
        ← Volver
    </a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';