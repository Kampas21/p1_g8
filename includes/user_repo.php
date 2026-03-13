<?php
declare(strict_types=1);

require_once __DIR__ . '/application.php';
require_once __DIR__ . '/util.php';


function user_row_to_avatar_url(array $row): string {
    $tipo = (string)($row['avatar_tipo'] ?? 'default');
    $valor = (string)($row['avatar_valor'] ?? '');

    if ($tipo === 'custom' && $valor !== '') {
        return RUTA_APP . '/' . ltrim($valor, '/');
    }

    if ($tipo === 'preset') {
        $presets = avatar_presets();
        if (isset($presets[$valor])) {
            return $presets[$valor]['path'];
        }
    }

    return avatar_default_path();
}

function user_find_by_id(int $id, bool $includeInactive = true): ?array {
    $conn = crearConexion();

    if ($includeInactive) {
        $sql = "SELECT * FROM usuarios WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    } else {
        $sql = "SELECT * FROM usuarios WHERE id = ? AND activo = 1 LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    if (!$row) {
        return null;
    }

    $row['avatar_url'] = user_row_to_avatar_url($row);
    return $row;
}

function user_find_by_username_or_email(string $login): ?array {
    $conn = crearConexion();
    $login = trim($login);

    $sql = "SELECT * 
            FROM usuarios
            WHERE activo = 1
              AND (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?))
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $conn->close();

    if (!$row) {
        return null;
    }

    $row['avatar_url'] = user_row_to_avatar_url($row);
    return $row;
}

function user_list(array $opts = []): array {
    $conn = crearConexion();

    $where = [];
    $params = [];
    $types = '';

    if (!($opts['include_inactive'] ?? false)) {
        $where[] = 'activo = 1';
    }

    if (!empty($opts['search'])) {
        $where[] = '(username LIKE ? OR email LIKE ? OR nombre LIKE ? OR apellidos LIKE ?)';
        $q = '%' . trim((string)$opts['search']) . '%';
        $params[] = $q;
        $params[] = $q;
        $params[] = $q;
        $params[] = $q;
        $types .= 'ssss';
    }

    $sql = 'SELECT * FROM usuarios';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY activo DESC, username ASC';

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $row['avatar_url'] = user_row_to_avatar_url($row);
        $rows[] = $row;
    }

    $stmt->close();
    $conn->close();

    return $rows;
}

function user_validate_data(array $input, bool $isCreate, ?int $editingId = null, bool $allowRoleEdit = false): array {
    $errors = [];
    $clean = [];

    $clean['username'] = trim((string)($input['username'] ?? ''));
    $clean['email'] = trim((string)($input['email'] ?? ''));
    $clean['nombre'] = trim((string)($input['nombre'] ?? ''));
    $clean['apellidos'] = trim((string)($input['apellidos'] ?? ''));
    $clean['rol'] = trim((string)($input['rol'] ?? 'cliente'));
    $clean['password'] = (string)($input['password'] ?? '');
    $clean['password_confirm'] = (string)($input['password_confirm'] ?? '');

    if ($clean['username'] === '' || mb_strlen($clean['username']) < 3) {
        $errors['username'] = 'El nombre de usuario debe tener al menos 3 caracteres.';
    }

    if (!filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Debes indicar un email válido.';
    }

    if ($clean['nombre'] === '') {
        $errors['nombre'] = 'El nombre es obligatorio.';
    }

    if ($clean['apellidos'] === '') {
        $errors['apellidos'] = 'Los apellidos son obligatorios.';
    }

    if ($isCreate || $clean['password'] !== '' || $clean['password_confirm'] !== '') {
        if (mb_strlen($clean['password']) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if ($clean['password'] !== $clean['password_confirm']) {
            $errors['password_confirm'] = 'Las contraseñas no coinciden.';
        }
    }

    if (!$allowRoleEdit) {
        $clean['rol'] = 'cliente';
    } elseif (!in_array($clean['rol'], valid_roles(), true)) {
        $errors['rol'] = 'Rol no válido.';
    }

    $conn = crearConexion();

    if (!isset($errors['username']) && $clean['username'] !== '') {
        if ($editingId === null) {
            $sql = "SELECT id FROM usuarios WHERE LOWER(username) = LOWER(?) LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $clean['username']);
        } else {
            $sql = "SELECT id FROM usuarios WHERE LOWER(username) = LOWER(?) AND id != ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $clean['username'], $editingId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $errors['username'] = 'Ya existe un usuario con ese nombre de usuario.';
        }
        $stmt->close();
    }

    if (!isset($errors['email'])) {
        if ($editingId === null) {
            $sql = "SELECT id FROM usuarios WHERE LOWER(email) = LOWER(?) LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $clean['email']);
        } else {
            $sql = "SELECT id FROM usuarios WHERE LOWER(email) = LOWER(?) AND id != ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $clean['email'], $editingId);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            $errors['email'] = 'Ya existe un usuario con ese email.';
        }
        $stmt->close();
    }

    $conn->close();

    return [$clean, $errors];
}

function user_create(array $data, array $avatarChoice): int {
    $conn = crearConexion();

    $sql = "INSERT INTO usuarios
            (username, email, nombre, apellidos, password_hash, rol, avatar_tipo, avatar_valor, activo, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    $rol = $data['rol'] ?? 'cliente';
    $avatarTipo = $avatarChoice['type'] ?? 'default';
    $avatarValor = $avatarChoice['value'] ?? null;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssss",
        $data['username'],
        $data['email'],
        $data['nombre'],
        $data['apellidos'],
        $passwordHash,
        $rol,
        $avatarTipo,
        $avatarValor
    );

    $stmt->execute();
    $newId = (int)$conn->insert_id;

    $stmt->close();
    $conn->close();

    return $newId;
}

function user_update(int $id, array $data, array $opts = []): void {
    $conn = crearConexion();
    $existing = user_find_by_id($id);

    if (!$existing) {
        $conn->close();
        throw new RuntimeException('Usuario no encontrado.');
    }

    $set = [
        'username = ?',
        'email = ?',
        'nombre = ?',
        'apellidos = ?',
        'updated_at = NOW()',
    ];

    $params = [
        $data['username'],
        $data['email'],
        $data['nombre'],
        $data['apellidos'],
    ];

    $types = 'ssss';

    if (($opts['allow_role'] ?? false) === true) {
        $set[] = 'rol = ?';
        $params[] = $data['rol'];
        $types .= 's';
    }

    if (!empty($data['password'])) {
        $set[] = 'password_hash = ?';
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= 's';
    }

    if (isset($opts['avatar_choice'])) {
        $avatarChoice = $opts['avatar_choice'];
        $set[] = 'avatar_tipo = ?';
        $set[] = 'avatar_valor = ?';
        $params[] = $avatarChoice['type'] ?? 'default';
        $params[] = $avatarChoice['value'] ?? null;
        $types .= 'ss';

        if (($existing['avatar_tipo'] ?? '') === 'custom') {
            $old = (string)($existing['avatar_valor'] ?? '');
            $new = (string)($avatarChoice['value'] ?? '');
            if ($old !== '' && $old !== $new) {
                delete_custom_avatar_file($old);
            }
        }
    }

    $sql = 'UPDATE usuarios SET ' . implode(', ', $set) . ' WHERE id = ?';
    $params[] = $id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function user_soft_delete(int $id): void {
    $conn = crearConexion();
    $user = user_find_by_id($id);

    if (!$user) {
        $conn->close();
        throw new RuntimeException('Usuario no encontrado.');
    }

    $sql = "UPDATE usuarios
            SET activo = 0, deleted_at = NOW(), updated_at = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function user_reactivate(int $id): void {
    $conn = crearConexion();

    $sql = "UPDATE usuarios
            SET activo = 1, deleted_at = NULL, updated_at = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
}

function user_remove_custom_avatar(int $id): void {
    $user = user_find_by_id($id);
    if (!$user) {
        throw new RuntimeException('Usuario no encontrado.');
    }

    if (($user['avatar_tipo'] ?? '') === 'custom') {
        delete_custom_avatar_file((string)($user['avatar_valor'] ?? ''));
    }

    $conn = crearConexion();

    $tipo = 'default';
    $sql = "UPDATE usuarios
            SET avatar_tipo = ?, avatar_valor = NULL, updated_at = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $tipo, $id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
}