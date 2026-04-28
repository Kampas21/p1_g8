<?php
session_start();

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/CategoriaDAO.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ../pedidos/catalogo.php');
    exit();
}

$producto = ProductoDAO::getById($id);

if (!$producto) {
    header('Location: ../pedidos/catalogo.php');
    exit();
}

$categoria = CategoriaDAO::getById($producto->getCategoriaId());
$nombreCategoria = $categoria ? $categoria->getNombre() : 'Sin categoría';

$tituloPagina = 'Detalle de Producto | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<main>
    <div class="panel">
        <h2>Detalles de <?= htmlspecialchars($producto->getNombre()) ?></h2>
        
        <div class="producto-detalle-contenedor">
            <?php if ($producto->getImagen()): ?>
                <div class="producto-imagen">
                    <img src="<?= htmlspecialchars(RUTA_APP . '/' . $producto->getImagen()) ?>" alt="<?= htmlspecialchars($producto->getNombre()) ?>">
                </div>
            <?php endif; ?>
            
            <div class="producto-info">
                <p><strong>Categoría:</strong> <?= htmlspecialchars($nombreCategoria) ?></p>
                <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($producto->getDescripcion())) ?></p>
                <p><strong>Precio (sin IVA):</strong> <?= number_format((float)$producto->getPrecio(), 2) ?> €</p>
                <p><strong>IVA:</strong> <?= (int)$producto->getIVA() ?>%</p>
                <p class="precio-final">Precio Final: <?= number_format((float)$producto->getPrecioFinal(), 2) ?> €</p>
                
                <div class="actions-inline mt-14">
                    <a href="javascript:history.back()" class="btn">← Volver</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>