<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = function_exists('current_user') ? current_user() : null;
?>


<nav aria-label="Navegación principal">
    <h3>Menú</h3>
    <ul class="menu-izq">
        <!-- Enlaces públicos para todos (logueados o no) -->
        <li><a href="<?= RUTA_APP ?>/index.php">Inicio</a></li>
        <li><a href="<?= RUTA_APP ?>/vistas/pedidos/elegirTipo.php">Nuevo pedido</a></li>
        <li><a href="<?= RUTA_APP ?>/vistas/ofertas/ofertaCliente.php">Nuestras ofertas</a></li>

        <!-- Enlaces solo para usuarios logueados -->
        <?php if ($user): ?>
            <h3>Panel gerente</h3>
            <!-- Categorías y Ofertas: Solo Gerente -->
            <?php if ($user->getRol() === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/categorias/categoriasList.php">Categorías</a></li>
                <li><a href="<?= RUTA_APP ?>/vistas/ofertas/listarOfertas.php">Ofertas</a></li>
            <?php endif; ?>
            
            <!-- Panel Camarero: Solo Camareros y Gerentes -->
            <?php if ($user->getRol() === 'camarero' || $user->getRol() === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/pedidos/gestionCamarero.php">Panel Camarero</a></li>
            <?php endif; ?>

            <!-- Panel Cocinero: Solo Cocinero y Gerente -->
            <?php if ($user->getRol() === 'cocinero' || $user->getRol() === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/preparacion_pedidos/panel_cocinero.php">Panel Cocinero</a></li>
            <?php endif; ?>

            <!-- Panel Gerente: Solo Gerente -->
            <?php if ($user->getRol() === 'gerente'): ?>
                <li><a href="<?= RUTA_APP ?>/vistas/preparacion_pedidos/panel_gerente.php">Panel Gerente</a></li>
            <?php endif; ?>
                
        <?php endif; ?>
    </ul>
</nav>