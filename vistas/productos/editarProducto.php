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
        <p>No tienes permisos para editar productos.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$id) {
    die('Producto no válido');
}

$producto = ProductoService::getById($id);

if (!$categoria_id) {
    $categoria_id = $producto->getCategoriaId();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria_id_post = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);

    if (!$categoria_id_post) {
        $categoria_id_post = $categoria_id;
    }

    if ($nombre !== '' && $descripcion !== '' && $categoria_id_post && $precio !== false && $iva !== false) {
        ProductoService::actualizar($id, $nombre, $descripcion, $categoria_id_post, $precio, $iva);
        header('Location: mostrarProductosCategoria.php?id=' . $categoria_id_post);
        exit;
    }
}

$tituloPagina = 'Editar producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Editar producto</h1>

<div class="panel" style="max-width: 720px;">
    <form method="POST">
        <input type="hidden" name="categoria_id" value="<?= htmlspecialchars((string)$categoria_id) ?>">

        <p>
            <label for="nombre"><strong>Nombre</strong></label><br>
            <input
                type="text"
                id="nombre"
                name="nombre"
                value="<?= htmlspecialchars($producto->getNombre()) ?>"
                required
                style="width:100%; max-width:400px;"
            >
        </p>

        <p>
            <label for="descripcion"><strong>Descripción</strong></label><br>
            <textarea
                id="descripcion"
                name="descripcion"
                rows="4"
                required
                style="width:100%; max-width:500px;"
            ><?= htmlspecialchars($producto->getDescripcion()) ?></textarea>
        </p>

        <p>
            <label for="precio"><strong>Precio base</strong></label><br>
            <input
                type="number"
                step="0.01"
                min="0"
                id="precio"
                name="precio"
                value="<?= htmlspecialchars((string)$producto->getPrecio()) ?>"
                required
            >
        </p>

        <p>
            <label for="iva"><strong>IVA</strong></label><br>
            <select id="iva" name="iva" required>
                <option value="4" <?= ((int)$producto->getIVA() === 4) ? 'selected' : '' ?>>4%</option>
                <option value="10" <?= ((int)$producto->getIVA() === 10) ? 'selected' : '' ?>>10%</option>
                <option value="21" <?= ((int)$producto->getIVA() === 21) ? 'selected' : '' ?>>21%</option>
            </select>
        </p>

        <?php
        $precioBase = (float)$producto->getPrecio();
        $ivaActual = (float)$producto->getIVA();
        $precioFinal = Producto::getPrecioFinal($precioBase, $ivaActual);
        ?>

        <div style="margin: 15px 0;">
            <p><strong>Precio base actual:</strong> <?= number_format($precioBase, 2) ?> €</p>
            <p><strong>IVA actual:</strong> <?= number_format($ivaActual, 0) ?> %</p>
            <p><strong>Precio final actual:</strong> <?= number_format($precioFinal, 2) ?> €</p>
        </div>

        <p style="margin-top:20px;">
            <button class="btn editar" type="submit">Actualizar</button>
            <a class="btn-volver" href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">← Volver</a>
        </p>
    </form>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';