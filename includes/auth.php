<?php
declare(strict_types=1);

require_once __DIR__ . '/UsuarioDAO.php';

function current_user(): ?Usuario {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $id = (int)$_SESSION['user_id'];
    $user = UsuarioDAO::user_find_by_id($id);
    if (!$user || !$user->isActivo()) {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function login_user(Usuario $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user->getId();
}
function logout_user(): void {
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

function require_login(): Usuario {
    $user = current_user();
    if (!$user) {
        flash_set('error', 'Debes iniciar sesión para acceder a esa página.');
        redirect(RUTA_APP . '/vistas/usuarios/acceso.php#login');
    }
    return $user;
}

function user_has_role(Usuario $user, string $minRole): bool {
    return UsuarioDAO::role_priority($user->getRol()) >= UsuarioDAO::role_priority($minRole);
}

function require_role(string $minRole): Usuario {
    $user = require_login();
    if (!user_has_role($user, $minRole)) {
        http_response_code(403);
        
        $tituloPagina = 'Acceso denegado | Bistro FDI';
        $rutaCSS = RUTA_APP . '/CSS/estilo.css';
        
        ob_start();
        ?>
        <div class="panel">
            <h2>Acceso denegado</h2>
            <p>No tienes permisos suficientes para esta acción.</p>
            <p><a class="btn primary" href="<?= RUTA_APP ?>/index.php">Volver al inicio</a></p>
        </div>
        <?php
        $contenidoPrincipal = ob_get_clean();
        require __DIR__ . '/plantilla.php';
        
        exit;
    }
    return $user;
}