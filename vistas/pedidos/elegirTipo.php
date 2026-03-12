<?php
require_once __DIR__ . '/../../entities/pedido.php';

/*
// Verificar que el usuario está logueado
$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    header('Location: /vistas/usuarios/login.php');
    exit();
}


// Si ya tiene un pedido en estado 'nuevo', redirigir al carrito directamente
$pedidoActivo = Pedido::getPedidoNuevo($usuario_id);
if ($pedidoActivo) {
    header('Location: carrito.php');
    exit();
}

*/

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? null;

    if ($tipo === 'local' || $tipo === 'llevar') {
        $pedido_id = Pedido::crearPedido($usuario_id, $tipo);
        header('Location: catalogo.php');
        exit();
    }

    $error = "Debes elegir un tipo de pedido.";
}
$tituloPagina = 'Elegir pedido';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<h1>Nuevo Pedido</h1>

<p>¿Cómo quieres tu pedido?</p>

<?php if (isset($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="elegirTipo.php">
    <button type="submit" name="tipo" value="local">
        🍽️ Consumir en el local
    </button>
    <button type="submit" name="tipo" value="llevar">
        🥡 Para llevar
    </button>
</form>


<?php


$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';