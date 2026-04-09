<?php

$modoEdicion = isset($categoria) && $categoria !== null;

$action = $modoEdicion
    ? "editarCategoria.php?id=" . $categoria->getId()
    : "crearCategoria.php";

$nombre = $modoEdicion ? $categoria->getNombre() : '';
$descripcion = $modoEdicion ? $categoria->getDescripcion() : '';

$tituloPagina = $modoEdicion ? 'Editar categoría' : 'Nueva categoría';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1><?= $modoEdicion ? 'Editar categoría' : 'Nueva categoría' ?></h1>

<?php if (!empty($errores)): ?>
    <div class="text-danger">
        <ul>
            <?php foreach ($errores as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>">

    <p>
        <label>Nombre:</label><br>
        <input type="text" name="nombre"
               value="<?= htmlspecialchars($nombre) ?>"
               required minlength="3">
    </p>

    <p>
        <label>Descripción:</label><br>
        <textarea name="descripcion" required><?= htmlspecialchars($descripcion) ?></textarea>
    </p>

    <p>
        <button type="submit">Guardar</button>
    </p>

</form>

<p>
    <a href="categoriasList.php">← Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';