<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtenemos el usuario actual si la sesión está iniciada
$user = null;
if (function_exists('current_user')) {
    $user = current_user();
}
?>

<h3>Menú</h3>
<ul class="menu-izq">
    <!-- Enlaces públicos para todos (logueados o no) -->
    <li><a href="<?= RUTA_APP ?>/index.php">Inicio</a></li>
    <li><a href="<?= RUTA_APP ?>/vistas/pedidos/elegirTipo.php">Nuevo pedido</a></li>
    
    <!-- Enlaces solo para usuarios logueados -->
    <?php if ($user): ?>
        <li><a href="<?= RUTA_APP ?>/vistas/pedidos/listarPedidosCliente.php">Mis Pedidos</a></li>
        
        <!-- Categorías: Solo Administrador / Gerente -->
        <?php if ($user['rol'] === 'gerente'): ?>
            <li><a href="<?= RUTA_APP ?>/vistas/categorias/categoriasList.php">Categorías</a></li>
        <?php endif; ?>
        
        <!-- Panel Camarero: Solo Camareros y Gerentes -->
        <?php if ($user['rol'] === 'camarero' || $user['rol'] === 'gerente'): ?>
            <li><a href="<?= RUTA_APP ?>/vistas/pedidos/gestionCamarero.php">Panel Camarero</a></li>
        <?php endif; ?>

        <!-- Panel Cocinero: Solo Cocineros y Gerentes -->
        <?php if ($user['rol'] === 'cocinero' || $user['rol'] === 'gerente'): ?>
            <li><a href="<?= RUTA_APP ?>/vistas/preparacion_pedidos/panel_cocinero.php">Panel Cocinero</a></li>
        <?php endif; ?>

        <!-- Panel Gerente: Solo Gerentes -->
        <?php if ($user['rol'] === 'gerente'): ?>
            <li><a href="<?= RUTA_APP ?>/vistas/preparacion_pedidos/panel_gerente.php">Panel Gerente</a></li>
        <?php endif; ?>
        
    <?php endif; ?>
</ul>