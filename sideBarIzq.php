<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = function_exists('current_user') ? current_user() : null;
?>

<nav class="sidebar-navegacion">
    <h3>Menú Principal</h3>
    <ul class="menu-izq">
        <li><a href="<?= RUTA_APP ?>/index.php">Inicio</a></li>
        <li><a href="<?= RUTA_APP ?>/vistas/pedidos/elegirTipo.php">Nuevo pedido</a></li>
        
        <?php if ($user): ?>
            
            <!-- Vistas Generales / Gerente -->
            <?php if ($user['rol'] === 'gerente'): ?>
                
                <li><a href="<?= RUTA_APP ?>/entities/usuarios.php">Usuarios (Gerente)</a></li>
    
                <li><a href="<?= RUTA_APP ?>/vistas/categorias/categoriasList.php">Categorías</a></li>
            <?php endif; ?>
            
            <?php if ($user['rol'] === 'camarero' || $user['rol'] === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/pedidos/gestionCamarero.php">Panel Camarero</a></li>
            <?php endif; ?>

            <?php if ($user['rol'] === 'cocinero' || $user['rol'] === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/preparacion_pedidos/panel_cocinero.php">Panel Cocinero</a></li>
            <?php endif; ?>

            <?php if ($user['rol'] === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/preparacion_pedidos/panel_gerente.php">Panel Gerente</a></li>
            <?php endif; ?>
            
        <?php endif; ?>
    </ul>
</nav>