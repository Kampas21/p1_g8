<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/../../entities/categoria.php';

$categorias = Categoria::getCategorias();

$tituloPagina = 'Lista de Categorías';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Lista de Categorías</h1>

<p>
    <a class="btn-nuevo"href="crearCategoria.php">Nueva Categoría</a>
</p>


<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Descripción</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>

    <?php foreach($categorias as $cat): ?>
    <tr>
        <td><?= $cat['id'] ?></td>
        <td><?= htmlspecialchars($cat['nombre']) ?></td>
        <td class="descripcion"><?= htmlspecialchars($cat['descripcion']) ?></td>
        <td>
            <?= $cat['activa']
                ? '<span style="color:green">Activa</span>'
                : '<span style="color:red">Inactiva</span>' ?>
        </td>
        <td>
            <a class="btn editar" href="editarCategoria.php?id=<?= $cat['id'] ?>">Editar</a>

            <a class="btn prod" href="../productos/mostrarProductosCategoria.php?id=<?= $cat['id'] ?>">
                Productos
            </a>

            <?php if ($cat['activa']): ?>
                <a class="btn activar" href="borrarCategoria.php?id=<?= $cat['id'] ?>"
                   onclick="return confirm('¿Seguro que quieres desactivar esta categoría?')">
                    Desactivar
                </a>
            <?php else: ?>
                <a class="btn borrar" href="activarCategoria.php?id=<?= $cat['id'] ?>">
                    Activar
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<p>
    <a class="btn-volver" href="../../index.php">Volver al inicio</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();

require __DIR__ . '/../../includes/plantilla.php';