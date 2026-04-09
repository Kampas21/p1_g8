require_once __DIR__ . '/../../includes/productoService.php';

$categoria_id = (int)$_GET['categoria_id'];

$productos = ProductoService::getAllByCategoria($categoria_id);

require_once __DIR__ . '/../../vistas/productos/mostrarProductosCategoria.php';