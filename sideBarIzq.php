<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<h3>Menú</h3>
<ul class="menu-izq">
    <li><a href="/P1/p1_g8/index.php">Inicio</a></li>
    <li><a href="/P1/p1_g8/vistas/categorias/categoriasList.php">Categorías</a></li>
    <li><a href="/P1/p1_g8/vistas/pedidos/elegirTipo.php">Nuevo pedido</a></li>
    <li><a href="/P1/p1_g8/vistas/pedidos/pedidosList.php">Pedidos</a></li>
</ul>