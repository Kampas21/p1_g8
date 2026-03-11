<?php
declare(strict_types=1);

require_once __DIR__ . '/user_repo.php';

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $id = (int)$_SESSION['user_id'];
    $user = user_find_by_id($id);
    if (!$user || (int)$user['activo'] !== 1) {
        unset($_SESSION['user_id']);
        return null;
    }
    return $user;
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
}

function logout_user(): void {
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        flash_set('error', 'Debes iniciar sesión para acceder a esa página.');
        redirect('acceso.php#login');
    }
    return $user;
}

function user_has_role(array $user, string $minRole): bool {
    return role_priority((string)$user['rol']) >= role_priority($minRole);
}

function require_role(string $minRole): array {
    $user = require_login();
    if (!user_has_role($user, $minRole)) {
        http_response_code(403);
        require_once __DIR__ . '/layout.php';
        layout_header('Acceso denegado');
        echo '<main><div class="panel"><h2>Acceso denegado</h2><p>No tienes permisos suficientes para esta acción.</p>';
        echo '<p><a class="btn" href="index.php">Volver al inicio</a></p></div></main>';
        layout_footer();
        exit;
    }
    return $user;
}
