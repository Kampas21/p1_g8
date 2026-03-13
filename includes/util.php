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

function avatar_presets(): array {
    return [
        'preset_chef' => ['label' => 'Opcion 1', 'path' => RUTA_APP . '/img/avatares/cocinero.png'],
        'preset_waiter' => ['label' => 'Opcion 2', 'path' => RUTA_APP . '/img/avatares/camarero.png'],
        'preset_manager' => ['label' => 'Opcion 3', 'path' => RUTA_APP . '/img/avatares/gerente.png'],
    ];
}

function default_avatar(): string {
    return RUTA_APP . '/img/avatares/default.png';
}

function valid_roles(): array {
    return ['cliente', 'camarero', 'cocinero', 'gerente'];
}

function role_priority(string $role): int {
    return match ($role) {
        'cliente' => 1,
        'camarero' => 2,
        'cocinero' => 3,
        'gerente' => 4,
        default => 0,
    };
}

function role_label(string $role): string {
    return ucfirst($role);
}

function upload_avatar_from_request(string $fieldName = 'avatar_upload'): ?array {
    if (!isset($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];

    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir el avatar (código ' . $error . ').');
    }

    $tmpPath = (string)$file['tmp_name'];
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > 2 * 1024 * 1024) {
        throw new RuntimeException('El avatar debe ocupar como máximo 2 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpPath) ?: '';
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => null,
    };

    if ($ext === null) {
        throw new RuntimeException('Formato de avatar no permitido. Usa JPG, PNG, WEBP o GIF.');
    }

    $uploadsDir = __DIR__ . '/../img/avatares';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0775, true);
    }

    $filename = 'avatar_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
    $destPath = $uploadsDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $destPath)) {
        throw new RuntimeException('No se pudo guardar el archivo del avatar.');
    }

    return [
        'type' => 'custom',
        'value' => $filename,
    ];
}


function resolve_avatar_choice_from_request(?array $currentUser = null, bool $isCreate = false): array {
    $mode = (string)($_POST['avatar_mode'] ?? ($isCreate ? 'default' : 'keep'));

    if (!$isCreate && $mode === 'keep') {
        return [
            'type' => (string)($currentUser['avatar_tipo'] ?? 'default'),
            'value' => ($currentUser['avatar_valor'] ?? null),
        ];
    }

    if ($mode === 'default' || $mode === 'remove_custom') {
        return ['type' => 'default', 'value' => null];
    }

    if ($mode === 'preset') {
        $preset = (string)($_POST['avatar_preset'] ?? '');
        $presets = avatar_presets();
        if (!isset($presets[$preset])) {
            throw new RuntimeException('Debes seleccionar un avatar predefinido válido.');
        }
        return ['type' => 'preset', 'value' => $preset];
    }

    if ($mode === 'upload') {
        $uploaded = upload_avatar_from_request('avatar_upload');
        if ($uploaded === null) {
            throw new RuntimeException('Debes seleccionar un archivo para subir como avatar.');
        }
        return $uploaded;
    }

    throw new RuntimeException('Opción de avatar no válida.');
}


function delete_custom_avatar_file(string $relativePath): void {
    $relativePath = ltrim($relativePath, '/');
    $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}
