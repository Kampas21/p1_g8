<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_post(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function redirect_back(string $fallback): never {
    $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
    redirect($target);
}

function flash_set(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    
    // Evitar meter el mensaje 10 veces si ya está en la cola
    $existe = false;
    foreach ($_SESSION['flash'] as $f) {
        if ($f['type'] === $type && $f['message'] === $message) {
            $existe = true;
            break;
        }
    }
    
    if (!$existe) {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }
}

function flash_get_all(): array {
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($items) ? $items : [];
}

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf']) || $_SESSION['csrf'] === '') {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_validate(?string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    if (!isset($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf'], $token);
}

function require_csrf(): void {
    if (!csrf_validate($_POST['csrf'] ?? null)) {
        http_response_code(400);
        echo '<h1>Error 400</h1><p>Token CSRF no válido.</p>';
        exit;
    }
}

