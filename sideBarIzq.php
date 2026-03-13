<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<h3>Menú</h3>
<ul class="menu-izq">
    <li><a href="<?= RUTA_APP ?>/index.php">Inicio</a></li>
    <li><a href="<?= RUTA_APP ?>/vistas/categorias/categoriasList.php">Categorías</a></li>
    <li><a href="<?= RUTA_APP ?>/vistas/pedidos/elegirTipo.php">Nuevo pedido</a></li>
    <li><a href="<?= RUTA_APP ?>/vistas/pedidos/pedidosList.php">Pedidos</a></li>
</ul>