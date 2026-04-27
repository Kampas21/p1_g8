<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/UsuarioDAO.php';
require_once __DIR__ . '/../../entities/pedido.php'; // Incluimos la Entidad Pedido
require_once __DIR__ . '/../../includes/Formulario/FormularioPerfil.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

use es\ucm\fdi\aw\Formulario\FormularioPerfil;

$user = require_login();

// Si piden quitar el avatar (POST)
if (is_post() && ($_POST['accion'] ?? '') === 'quitar_avatar') {
    require_csrf();
    UsuarioDAO::user_remove_custom_avatar($user->getId());
    flash_set('success', 'Avatar personalizado eliminado.');
    header("Location: perfil.php");
    exit();
}

// Inicialización de vista y Formulario
$form = new FormularioPerfil();
$htmlFormPerfil = $form->gestiona();

// Aquí hemos eliminado TODO el SQL en crudo. Delega a los modelos:
$pedidosActivos = [];
$pedidosHistorico = [];
$pedidosDisponibles = false;

$uid = $user->getId();
$tab = $_GET['tab'] ?? 'datos'; // Saber en qué pestaña estamos

try {
    if ($tab === 'activos') {
        // cargamos solo los pedidos activos si el usuario está en la pestaña activos
        $pedidosActivos = PedidoService::getPedidosActivosByUsuario($uid);
    } 
    elseif ($tab === 'historico') {
        // cargamos solo el histórico si el usuario está en la pestaña historico
        $pedidosHistorico = PedidoService::getPedidosHistoricoByUsuario($uid);
    }
    
    $numPedidosActivos = PedidoService::contarPedidosActivosByUsuario($uid);
    $pedidosDisponibles = true;
} catch (\Exception $e) {
    $pedidosDisponibles = false;
}

$tituloPagina = 'Perfil | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<div class="panel">
    <h2>Mi Cuenta</h2>
    
    <!-- Menú de Pestañas (Submenú rápido) -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="perfil.php?tab=datos" class="btn <?= $tab === 'datos' ? 'editar' : '' ?>">👤 Configuración de Perfil</a>
        <a href="perfil.php?tab=activos" class="btn <?= $tab === 'activos' ? 'editar' : '' ?>">⏳ Pedidos Activos (<?= $numPedidosActivos ?>)</a>
        <a href="perfil.php?tab=historico" class="btn <?= $tab === 'historico' ? 'editar' : '' ?>">📜 Histórico de Pedidos</a>
    </div>

    <?php foreach (flash_get_all() as $item): ?>
        <?php $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info'; ?>
        <div class="notice <?= e($type) ?>"><?= e($item['message']) ?></div>
    <?php endforeach; ?>
</div>

<main class="profile-layout">

    <?php if ($tab === 'datos'): ?>
    <!-- 1. PESTAÑA: Todo lo del formulario, avatar y detalles del usuario -->
    <section class="panel profile-card" style="grid-column: 1 / -1;">
        <h3>Mis Datos</h3>

        <div class="profile-top" style="align-items: center; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
            <div style="text-align: center;">
                <img class="avatar lg" style="margin-bottom: 8px;" src="<?= e($user->getAvatarUrl()) ?>" alt="Avatar de <?= e($user->getUsername()) ?>">
                
                <?php if ($user->getAvatarTipo() === 'custom'): ?>
                    <form method="post" onsubmit="return confirm('¿Quitar avatar personalizado?');">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="accion" value="quitar_avatar">
                        <button class="btn danger small" type="submit">Eliminar foto</button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div style="margin-left: 10px;">
                <p><strong>Usuario:</strong> <?= e($user->getUsername()) ?></p>
                <p><strong>Email:</strong> <?= e($user->getEmail()) ?></p>
                <p><strong>Nombre y apellidos:</strong> <?= e($user->getNombre()) ?> <?= e($user->getApellidos()) ?></p>
                <p><strong>Rol:</strong> <?= e(UsuarioDAO::role_label((string)$user->getRol())) ?></p>
            </div>
        </div>
        
        <div class="mt-20">
            <!-- Renderizamos el FormularioPerfil configurado con clases POO -->
            <?= $htmlFormPerfil ?>
        </div>
    </section>
    <?php endif; ?>


    <?php if ($tab === 'activos'): ?>
    <!-- 2. PESTAÑA: Pedidos en curso -->
    <section style="grid-column: 1 / -1;">
        <?php include __DIR__ . '/_pedidos_activos.php'; ?>
    </section>
    <?php endif; ?>


    <?php if ($tab === 'historico'): ?>
    <!-- 3. PESTAÑA: Pedidos terminados/entregados -->
    <section style="grid-column: 1 / -1;">
        <?php include __DIR__ . '/_pedidos_historico.php'; ?>
    </section>
    <?php endif; ?>

</main> <!-- / profile-layout -->

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>