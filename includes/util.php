<?php
declare(strict_types=1);

/**
 * Escapa texto antes de mostrarlo en HTML.
 */
function escaparHtml(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


/**
 * Comprueba si la petición actual es POST.
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Redirige a una URL y termina la ejecución.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Redirige a la página anterior si existe.
 * Si no existe, redirige a la URL indicada como alternativa.
 */
function redirect_back(string $fallback): never
{
    $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
    redirect($target);
}

/**
 * Guarda un mensaje flash en sesión.
 */
function flash_set(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }

    foreach ($_SESSION['flash'] as $flash) {
        if (
            isset($flash['type'], $flash['message']) &&
            $flash['type'] === $type &&
            $flash['message'] === $message
        ) {
            return;
        }
    }

    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtiene todos los mensajes flash y los elimina de la sesión.
 */
function flash_get_all(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return is_array($items) ? $items : [];
}