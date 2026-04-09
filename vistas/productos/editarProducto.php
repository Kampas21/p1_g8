<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
    ?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Necesitas ser gerente para editar productos.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('Producto inválido');
}

$producto = ProductoService::getById($id);

if (!$producto) {
    die('Producto no encontrado');
}

$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);
if (!$categoria_id) {
    $categoria_id = $producto->getCategoriaId();
}

$categoria = CategoriaService::getById($categoria_id);
if (!$categoria) {
    die('Categoría no encontrada');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim((string) filter_input(INPUT_POST, 'nombre'));
    $descripcion = trim((string) filter_input(INPUT_POST, 'descripcion'));
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);

    if ($nombre === '' || mb_strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }

    if ($descripcion === '' || mb_strlen($descripcion) < 3) {
        $errores[] = 'La descripción debe tener al menos 3 caracteres.';
    }

    if ($precio === false || $precio < 0) {
        $errores[] = 'El precio debe ser un número válido mayor o igual que 0.';
    }

    if (!in_array($iva, [4, 10, 21], true)) {
        $errores[] = 'El IVA debe ser 4, 10 o 21.';
    }

    if (empty($errores)) {
        ProductoService::update(
            $id,
            $nombre,
            $descripcion,
            $categoria_id,
            $precio,
            $iva
        );

        header('Location: mostrarProductosCategoria.php?id=' . $categoria_id);
        exit;
    }
} else {
    $nombre = $producto->getNombre();
    $descripcion = $producto->getDescripcion();
    $precio = $producto->getPrecio();
    $iva = $producto->getIVA();
}

$tituloPagina = 'Editar producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Editar producto</h1>

<?php if (!empty($errores)): ?>
    <div class="error-box">
        <ul>
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" action="editarProducto.php?id=<?= $id ?>&categoria_id=<?= $categoria_id ?>">

        <p>
            <label for="nombre">Nombre:</label><br>
            <input
                type="text"
                name="nombre"
                id="nombre"
                required
                minlength="3"
                maxlength="100"
                value="<?= htmlspecialchars($nombre) ?>"
            >
        </p>

        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea
                name="descripcion"
                id="descripcion"
                rows="5"
                cols="40"
                required
                minlength="3"
                maxlength="255"
            ><?= htmlspecialchars($descripcion) ?></textarea>
        </p>

        <p>
            <label for="precio">Precio base (€):</label><br>
            <input
                type="number"
                name="precio"
                id="precio"
                step="0.01"
                min="0"
                required
                value="<?= htmlspecialchars((string) $precio) ?>"
            >
        </p>

        <p>
            <label for="iva">IVA:</label><br>
            <select name="iva" id="iva" required>
                <option value="4" <?= ((int)$iva === 4) ? 'selected' : '' ?>>4%</option>
                <option value="10" <?= ((int)$iva === 10) ? 'selected' : '' ?>>10%</option>
                <option value="21" <?= ((int)$iva === 21) ? 'selected' : '' ?>>21%</option>
            </select>
        </p>

        <p>
            <strong>Precio final:</strong>
            <span id="precioFinal">
                <?= number_format((float)$precio * (1 + ((int)$iva / 100)), 2) ?> €
            </span>
        </p>

        <p class="acciones-form">
            <button type="submit" class="btn-aceptar">Guardar cambios</button>
            <a class="btn-volver" href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">Cancelar</a>
        </p>
    </form>
</div>

<script>
(function () {
    const precioInput = document.getElementById('precio');
    const ivaSelect = document.getElementById('iva');
    const precioFinal = document.getElementById('precioFinal');

    function recalcularPrecioFinal() {
        const precio = parseFloat(precioInput.value);
        const iva = parseInt(ivaSelect.value);

        if (!isNaN(precio) && !isNaN(iva)) {
            const total = precio * (1 + iva / 100);
            precioFinal.textContent = total.toFixed(2) + ' €';
        } else {
            precioFinal.textContent = '-';
        }
    }

    precioInput.addEventListener('input', recalcularPrecioFinal);
    ivaSelect.addEventListener('change', recalcularPrecioFinal);
})();
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';