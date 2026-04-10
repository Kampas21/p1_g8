<?php
declare(strict_types=1);



require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/user_repo.php';
require_once __DIR__ . '/../../entities/pedido.php'; // Incluimos la Entidad Pedido
require_once __DIR__ . '/../../includes/Formulario/FormularioPerfil.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

use es\ucm\fdi\aw\Formulario\FormularioPerfil;

$user = require_login();

// Si piden quitar el avatar (POST)
if (is_post() && ($_POST['accion'] ?? '') === 'quitar_avatar') {
    require_csrf();
    user_remove_custom_avatar($user->getId());
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

$conn = crearConexion();
$checkTable = $conn->query("SHOW TABLES LIKE 'pedidos'");

if ($checkTable && $checkTable->num_rows > 0) {
    $pedidosDisponibles = true;
    $uid = $user->getId();
    
    $pedidosActivos = PedidoService::getPedidosActivosByUsuario($uid);
    $pedidosHistorico = PedidoService::getPedidosHistoricoByUsuario($uid);
}
$conn->close();

$tituloPagina = 'Perfil | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<div class="panel">
    <h2>Perfil y pedidos</h2>
    <?php foreach (flash_get_all() as $item): ?>
        <?php $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info'; ?>
        <div class="notice <?= e($type) ?>"><?= e($item['message']) ?></div>
    <?php endforeach; ?>
</div>

<main class="profile-layout">
    <section class="panel profile-card">
        <h3>Avatar + datos de usuario</h3>

        <div class="profile-top">
            <img class="avatar lg" src="<?= e($user->getAvatarUrl()) ?>" alt="Avatar de <?= e($user->getUsername()) ?>">
            <div>
                <p><strong>Usuario:</strong> <?= e($user->getUsername()) ?></p>
                <p><strong>Email:</strong> <?= e($user->getEmail()) ?></p>
                <p><strong>Nombre y apellidos:</strong> <?= e($user->getNombre()) ?> <?= e($user->getApellidos()) ?></p>
                <p><strong>Rol:</strong> <?= e(role_label((string)$user->getRol())) ?></p>
            </div>
        </div>
        
        <details class="mt-14" open>
            <summary>Foto de perfil / Avatar</summary>
            <img src="<?= e($user->getAvatarUrl()) ?>" alt="Avatar" class="avatar xl mb-15">

           <?php if ($user->getAvatarTipo() === 'custom'): ?>
                <!-- Botón eliminar foto nativo -->
                <form method="post" onsubmit="return confirm('¿Quitar avatar personalizado?');" class="mt-14">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="quitar_avatar">
                    <button class="btn danger" type="submit">Eliminar foto actual</button>
                </form>
            <?php endif; ?>
        </details>
        
        <div class="mt-20">
            <!-- Renderizamos el FormularioPerfil configurado con clases POO -->
            <?= $htmlFormPerfil ?>
        </div>
    </section>

    <!-- Usamos los scripts de apoyo (partials) para pintar las sub-vistas -->
    <?php include __DIR__ . '/_pedidos_activos.php'; ?>
    
</main> <!-- / profile-layout -->

<div class="mt-20">
    <?php include __DIR__ . '/_pedidos_historico.php'; ?>
</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
?>