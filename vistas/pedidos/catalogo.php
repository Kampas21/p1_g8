<?php
session_start();
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/application.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/util.php';
require_once __DIR__ . '/../../includes/categoriaService.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../entities/categoria.php';
require_once __DIR__ . '/../../includes/Formulario/FormularioAddCarrito.php';

$user = require_login();
$usuario_id = (int)$user->getId();

$pedido = Pedido::getPedidoNuevo($usuario_id);
if (!$pedido) {
    redirect('elegirTipo.php');
}
$pedido_id = $pedido['id'];

// Obtener categoría GET
$categoria_id = isset($_GET['categoria']) && is_numeric($_GET['categoria'])
    ? (int)$_GET['categoria']
    : null;

// Inicializamos el almacén de los formularios HTML
$formHtmls = [];

if ($categoria_id) {
    $productos = ProductoService::getAllByCategoria($categoria_id);
    $categoria = CategoriaService::getById($categoria_id);
    
    // Instanciamos un formulario por cada producto para que intercepte si hubo POST
    if (!empty($productos)) {
        foreach ($productos as $p) {
            $prod_id = (int)$p->getId();
            $formAdd = new \es\ucm\fdi\aw\Formulario\FormularioAddCarrito($pedido_id, $prod_id, $categoria_id);
            $formHtmls[$prod_id] = $formAdd->gestiona();
        }
    }
} else {
    $categorias = CategoriaService::getAll();
}

$titulo = $categoria_id && isset($categoria)
    ? 'Catálogo — ' . e($categoria->getNombre())
    : 'Catálogo';

// ---- EMPIEZA LA VISTA DEL PROYECTO ----
$tituloPagina = $titulo . ' | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';
ob_start();
?>

<main>
  <?php foreach (flash_get_all() as $f): ?>
      <div class="mensaje-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
  <?php endforeach; ?>

  <div class="panel">
    <?php if ($categoria_id): ?>

      <div class="actions-inline" style="margin-bottom:12px;">
        <a href="catalogo.php" class="btn">← Categorías</a>
        <a href="carrito.php" class="btn primary">🛒 Ver carrito</a>
      </div>

      <h2><?= e($categoria->getNombre()) ?></h2>

      <?php if (empty($productos)): ?>
        <p>No hay productos disponibles en esta categoría.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
              <tr>
                <td><?= e($p->getNombre()) ?></td>
                <td><?= e($p->getDescripcion()) ?></td>
                <td><?= $p->getPrecioFinal() ?> €</td>
                <td>
                  <!-- Aquí imprimimos la vista generada del formulario que instanciamos arriba -->
                  <?= $formHtmls[$p->getId()] ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    <?php else: ?>

      <div class="actions-inline" style="margin-bottom:12px;justify-content:flex-end;">
        <a href="carrito.php" class="btn primary">🛒 Ver carrito</a>
      </div>

      <h2>Elige una categoría</h2>

      <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:8px;">
        <?php foreach ($categorias as $cat): ?>
          <a href="catalogo.php?categoria=<?= (int)$cat->getId() ?>" class="btn" style="font-size:15px; padding:12px 20px;">
            <?= e($cat->getNombre()) ?>
          </a>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>
</main>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>