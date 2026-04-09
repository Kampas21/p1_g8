<?php
$modoEdicion = isset($categoria) && $categoria !== null;
$action = $modoEdicion ? "editarCategoria.php?id=" . $categoria->getId() : "crearCategoria.php";
$nombre = $modoEdicion ? $categoria->getNombre() : '';
$descripcion = $modoEdicion ? $categoria->getDescripcion() : '';
$tituloPagina = $modoEdicion ? 'Editar categoría' : 'Nueva categoría';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1><?= $modoEdicion ? 'Editar categoría' : 'Nueva categoría' ?></h1>

<?php if (!empty($error)): ?>
    <p style="color:red; font-weight:bold;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="<?= $action ?>">
    <p>
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre"
               value="<?= htmlspecialchars($nombre) ?>"
               required minlength="3" maxlength="100">
    </p>

    <p>
        <label for="descripcion">Descripción:</label><br>
        <textarea name="descripcion" id="descripcion" rows="5" cols="40"
                  required minlength="3" maxlength="255"><?= htmlspecialchars($descripcion) ?></textarea>
    </p>

    <p>
        <button type="submit" class="btn-aceptar">Guardar</button>
    </p>
</form>

<p>
    <a class="btn-volver" href="categoriasList.php">Volver al listado</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';