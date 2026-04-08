<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/productoService.php';

$user = current_user();

// 🔒 Control acceso (solo gerente)
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

// 📥 recoger id categoría
$categoria_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$categoria_id) {
    die("Categoría inválida");
}

// 📦 obtener productos
$productos = ProductoService::getAllByCategoria($categoria_id);

// 🎨 plantilla
$tituloPagina = 'Productos';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Productos</h1>

<p>
    <a href="crearProducto.php?categoria_id=<?= $categoria_id ?>">
        ➕ Crear producto
    </a>
</p>

<?php foreach ($productos as $p): ?>

    <div class="panel">

        <h3><?= htmlspecialchars($p['nombre']) ?></h3>

        <p><?= htmlspecialchars($p['descripcion']) ?></p>

        <p><strong><?= $p['precio_base'] ?> €</strong></p>

        <!-- Estado -->
        <?php if ($p['ofertado']): ?>
            <p style="color:green;">Disponible</p>
        <?php else: ?>
            <p style="color:red;">No ofertado</p>
        <?php endif; ?>

        <!-- Acciones -->
        <a href="editarProducto.php?id=<?= $p['id'] ?>&categoria_id=<?= $categoria_id ?>">
            Editar
        </a>

        <?php if (!$p['ofertado']): ?>
            <a href="activarProducto.php?id=<?= $p['id'] ?>&categoria_id=<?= $categoria_id ?>">
                Activar
            </a>
        <?php else: ?>
            <a href="eliminarProducto.php?id=<?= $p['id'] ?>&categoria_id=<?= $categoria_id ?>">
                Eliminar
            </a>
        <?php endif; ?>

    </div>

<?php endforeach; ?>

<p>
    <a class="btn-volver" href="../categorias/categoriasList.php">
        ← Volver a categorías
    </a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';