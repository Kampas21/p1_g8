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
        <p>Necesitas ser gerente para crear productos.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
    <?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

$categoria_id = filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die('Categoría inválida');
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
        $errores[] = 'El precio debe ser válido.';
    }

    if (!in_array($iva, [4, 10, 21], true)) {
        $errores[] = 'IVA inválido.';
    }

    if (empty($errores)) {
        ProductoService::create(
            $nombre,
            $descripcion,
            $categoria_id,
            $precio,
            $iva
        );

        header('Location: mostrarProductosCategoria.php?id=' . $categoria_id);
        exit;
    }
}

$tituloPagina = 'Nuevo producto';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Nuevo producto</h1>

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
    <form method="POST" action="crearProducto.php?categoria_id=<?= $categoria_id ?>">

        <p>
            <label>Nombre:</label><br>
            <input type="text" name="nombre" required minlength="3" maxlength="100">
        </p>

        <p>
            <label>Descripción:</label><br>
            <textarea name="descripcion" rows="5" required minlength="3"></textarea>
        </p>

        <p>
            <label>Precio base (€):</label><br>
            <input type="number" name="precio" step="0.01" min="0" required>
        </p>

        <p>
            <label>IVA:</label><br>
            <select name="iva" required>
                <option value="4">4%</option>
                <option value="10">10%</option>
                <option value="21" selected>21%</option>
            </select>
        </p>

        <p>
            <strong>Precio final:</strong>
            <span id="precioFinal">-</span>
        </p>

        <p class="acciones-form">
            <button type="submit" class="btn-aceptar">Crear producto</button>
            <a class="btn-volver" href="mostrarProductosCategoria.php?id=<?= $categoria_id ?>">Cancelar</a>
        </p>

    </form>
</div>

<script>
(function () {
    const precio = document.querySelector('[name="precio"]');
    const iva = document.querySelector('[name="iva"]');
    const total = document.getElementById('precioFinal');

    function calcular() {
        const p = parseFloat(precio.value);
        const i = parseInt(iva.value);

        if (!isNaN(p)) {
            total.textContent = (p * (1 + i / 100)).toFixed(2) + ' €';
        } else {
            total.textContent = '-';
        }
    }

    precio.addEventListener('input', calcular);
    iva.addEventListener('change', calcular);
})();
</script>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';