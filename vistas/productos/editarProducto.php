<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();

// 🔒 Solo gerente
if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

// 📌 Obtener ID producto
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Producto inválido");
}

// 📦 Obtener producto
$producto = ProductoService::getById($id);

if (!$producto) {
    die("Producto no encontrado");
}

// 📌 Categorías
$categorias = CategoriaService::getAll();

// 📌 Valores iniciales
$nombre = $producto->getNombre();
$descripcion = $producto->getDescripcion();
$categoria_id = $producto->getCategoriaId();
$precio = $producto->getPrecio();
$iva = $producto->getIVA();

$errores = [];

// 🔥 PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_FLOAT);

    // Validación
    if (!$nombre || strlen($nombre) < 3) {
        $errores[] = "Nombre inválido";
    }

    if (!$descripcion) {
        $errores[] = "Descripción obligatoria";
    }

    if (!$categoria_id) {
        $errores[] = "Categoría inválida";
    }

    if ($precio === false || $precio <= 0) {
        $errores[] = "Precio inválido";
    }

    if (!in_array($iva, [10, 21])) {
        $errores[] = "IVA inválido";
    }

    // ✅ actualizar
    if (empty($errores)) {

        ProductoService::update($id, $nombre, $descripcion, $categoria_id, $precio, $iva);

        header("Location: mostrarProductosCategoria.php?id=" . $categoria_id);
        exit;
    }
}

// 🎨 Vista
$tituloPagina = 'Editar Producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Editar producto</h1>

<?php if (!empty($errores)): ?>
    <div style="color:red;">
        <?php foreach ($errores as $e): ?>
            <p><?= $e ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST">

    <p>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
    </p>

    <p>
        <label>Descripción:</label><br>
        <textarea name="descripcion" required><?= htmlspecialchars($descripcion) ?></textarea>
    </p>

    <p>
        <label>Categoría:</label><br>
        <select name="categoria_id" required>
            <option value="">-- Seleccionar --</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= $c->getId() ?>" <?= ($categoria_id == $c->getId()) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c->getNombre()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>

    <p>
        <label>Precio (€):</label><br>
        <input type="number" step="0.01" name="precio" value="<?= $precio ?>" required>
    </p>

    <p>
        <label>IVA:</label><br>
        <select name="iva" required>
            <option value="10" <?= ($iva == 10) ? 'selected' : '' ?>>10%</option>
            <option value="21" <?= ($iva == 21) ? 'selected' : '' ?>>21%</option>
        </select>
    </p>

    <p>
        <button type="submit">Guardar cambios</button>
    </p>

</form>

<p>
    <a href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">← Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';