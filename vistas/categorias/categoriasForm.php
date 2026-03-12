<?php
$modoEdicion = isset($categoria);
$action = $modoEdicion ? "editarCategoria.php?id=" . $categoria['id'] : "crearCategoria.php";
$nombre = $modoEdicion ? $categoria['nombre'] : '';
$descripcion = $modoEdicion ? $categoria['descripcion'] : '';
$tituloPagina = 'Editar categoria';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">


<h1><?= $modoEdicion ? 'Editar categoría' : 'Nueva categoría' ?></h1>

<form method="POST" action="<?= $action ?>">
    <p>
        <label for="nombre">Nombre:</label><br>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
    </p>

    <p>
        <label for="descripcion">Descripción:</label><br>
        <textarea name="descripcion" id="descripcion" rows="5" cols="40" required><?= htmlspecialchars($descripcion) ?></textarea>
    </p>

    <p><button type="submit" class="btn-aceptar">Guardar</button></p>
</form>

<p>
    <a class="btn-volver" href="categoriasList.php">Volver al listado</a>
</p>
<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';