<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/categoriaService.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    die("Acceso denegado");
}

// 📌 categoría
$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die("Categoría inválida");
}

$categoria = CategoriaService::getById($categoria_id);

if (!$categoria) {
    die("Categoría no encontrada");
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);

    // VALIDACIÓN
    if (!$nombre || strlen($nombre) < 3) {
        $errores[] = "Nombre inválido";
    }

    if (!$descripcion) {
        $errores[] = "Descripción obligatoria";
    }

    if ($precio === false || $precio < 0) {
        $errores[] = "Precio inválido";
    }

    if (!in_array($iva, [4, 10, 21])) {
        $errores[] = "IVA inválido";
    }

    if (empty($errores)) {

        ProductoService::create(
            $nombre,
            $descripcion,
            $categoria_id,
            $precio,
            $iva
        );

        header("Location: mostrarProductosCategoria.php?id=" . $categoria_id);
        exit;
    }
}

$tituloPagina = 'Crear Producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Nuevo producto en <?= htmlspecialchars($categoria->getNombre()) ?></h1>

<?php if (!empty($errores)): ?>
    <ul style="color:red;">
        <?php foreach ($errores as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="POST">

    <label>Nombre:</label><br>
    <input type="text" name="nombre" required minlength="3"><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" required></textarea><br><br>

    <label>Precio base (€):</label><br>
    <input type="number" step="0.01" name="precio" required><br><br>

    <label>IVA:</label><br>
    <select name="iva" required>
        <option value="4">4%</option>
        <option value="10">10%</option>
        <option value="21">21%</option>
    </select><br><br>

    <p id="precioFinal"></p>

    <button type="submit">Crear</button>

</form>

<script>
const precioInput = document.querySelector('input[name="precio"]');
const ivaSelect = document.querySelector('select[name="iva"]');
const salida = document.getElementById('precioFinal');

function calcular() {
    const precio = parseFloat(precioInput.value);
    const iva = parseFloat(ivaSelect.value);

    if (!isNaN(precio) && !isNaN(iva)) {
        const total = precio * (1 + iva / 100);
        salida.textContent = "Precio final: " + total.toFixed(2) + " €";
    } else {
        salida.textContent = "";
    }
}

precioInput.addEventListener('input', calcular);
ivaSelect.addEventListener('change', calcular);
</script>

<br>
<a href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">← Volver</a>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';