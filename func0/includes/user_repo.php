<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/util.php';

function user_row_to_avatar_url(array $row): string {
    $tipo = (string)($row['avatar_tipo'] ?? 'default');
    $valor = (string)($row['avatar_valor'] ?? '');

    if ($tipo === 'custom' && $valor !== '') {
        return $valor;
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
    $pdo = get_pdo();
    $sql = 'SELECT * FROM usuarios WHERE id = :id';
    if (!$includeInactive) {
        $sql .= ' AND activo = 1';
    }
    $stmt = $pdo->prepare($sql . ' LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['avatar_url'] = user_row_to_avatar_url($row);
    return $row;
}

function user_find_by_username_or_email(string $login): ?array {
    $pdo = get_pdo();
    $stmt = $pdo->prepare(
        'SELECT * FROM usuarios 
         WHERE activo = 1 AND (LOWER(username) = LOWER(:login) OR LOWER(email) = LOWER(:login))
         LIMIT 1'
    );
    $stmt->execute(['login' => trim($login)]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['avatar_url'] = user_row_to_avatar_url($row);
    return $row;
}

function user_list(array $opts = []): array {
    $pdo = get_pdo();
    $where = [];
    $params = [];

    if (!($opts['include_inactive'] ?? false)) {
        $where[] = 'activo = 1';
    }

    if (!empty($opts['search'])) {
        $where[] = '(username LIKE :q OR email LIKE :q OR nombre LIKE :q OR apellidos LIKE :q)';
        $params['q'] = '%' . trim((string)$opts['search']) . '%';
    }

    $sql = 'SELECT * FROM usuarios';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY activo DESC, username COLLATE NOCASE ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['avatar_url'] = user_row_to_avatar_url($row);
    }
    unset($row);

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

    $pdo = get_pdo();

    if (!isset($errors['username']) && $clean['username'] !== '') {
        if ($editingId === null) {
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE LOWER(username)=LOWER(:u) LIMIT 1');
            $stmt->execute(['u' => $clean['username']]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE LOWER(username)=LOWER(:u) AND id != :id LIMIT 1');
            $stmt->execute(['u' => $clean['username'], 'id' => $editingId]);
        }
        if ($stmt->fetch()) {
            $errors['username'] = 'Ya existe un usuario con ese nombre de usuario.';
        }
    }

    if (!isset($errors['email'])) {
        if ($editingId === null) {
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE LOWER(email)=LOWER(:e) LIMIT 1');
            $stmt->execute(['e' => $clean['email']]);
        } else {
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE LOWER(email)=LOWER(:e) AND id != :id LIMIT 1');
            $stmt->execute(['e' => $clean['email'], 'id' => $editingId]);
        }
        if ($stmt->fetch()) {
            $errors['email'] = 'Ya existe un usuario con ese email.';
        }
    }

    return [$clean, $errors];
}

function user_create(array $data, array $avatarChoice): int {
    $pdo = get_pdo();

    $stmt = $pdo->prepare('
        INSERT INTO usuarios 
            (username, email, nombre, apellidos, password_hash, rol, avatar_tipo, avatar_valor, activo, created_at, updated_at)
        VALUES
            (:username, :email, :nombre, :apellidos, :password_hash, :rol, :avatar_tipo, :avatar_valor, 1, :created_at, :updated_at)
    ');

    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        'username' => $data['username'],
        'email' => $data['email'],
        'nombre' => $data['nombre'],
        'apellidos' => $data['apellidos'],
        'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        'rol' => $data['rol'] ?? 'cliente',
        'avatar_tipo' => $avatarChoice['type'] ?? 'default',
        'avatar_valor' => $avatarChoice['value'] ?? null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return (int)$pdo->lastInsertId();
}

function user_update(int $id, array $data, array $opts = []): void {
    $pdo = get_pdo();
    $existing = user_find_by_id($id);
    if (!$existing) {
        throw new RuntimeException('Usuario no encontrado.');
    }

    $set = [
        'username = :username',
        'email = :email',
        'nombre = :nombre',
        'apellidos = :apellidos',
        'updated_at = :updated_at',
    ];
    $params = [
        'id' => $id,
        'username' => $data['username'],
        'email' => $data['email'],
        'nombre' => $data['nombre'],
        'apellidos' => $data['apellidos'],
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    if (($opts['allow_role'] ?? false) === true) {
        $set[] = 'rol = :rol';
        $params['rol'] = $data['rol'];
    }

    if (!empty($data['password'])) {
        $set[] = 'password_hash = :password_hash';
        $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (isset($opts['avatar_choice'])) {
        $avatarChoice = $opts['avatar_choice'];
        $set[] = 'avatar_tipo = :avatar_tipo';
        $set[] = 'avatar_valor = :avatar_valor';
        $params['avatar_tipo'] = $avatarChoice['type'] ?? 'default';
        $params['avatar_valor'] = $avatarChoice['value'] ?? null;

        if (($existing['avatar_tipo'] ?? '') === 'custom') {
            $old = (string)($existing['avatar_valor'] ?? '');
            $new = (string)($params['avatar_valor'] ?? '');
            if ($old !== '' && $old !== $new) {
                delete_custom_avatar_file($old);
            }
        }
    }

    $sql = 'UPDATE usuarios SET ' . implode(', ', $set) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

function user_soft_delete(int $id): void {
    $pdo = get_pdo();
    $user = user_find_by_id($id);
    if (!$user) {
        throw new RuntimeException('Usuario no encontrado.');
    }

    $stmt = $pdo->prepare('UPDATE usuarios SET activo = 0, deleted_at = :deleted_at, updated_at = :updated_at WHERE id = :id');
    $now = date('Y-m-d H:i:s');
    $stmt->execute([
        'id' => $id,
        'deleted_at' => $now,
        'updated_at' => $now,
    ]);
}

function user_reactivate(int $id): void {
    $pdo = get_pdo();
    $stmt = $pdo->prepare('UPDATE usuarios SET activo = 1, deleted_at = NULL, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        'id' => $id,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

function user_remove_custom_avatar(int $id): void {
    $user = user_find_by_id($id);
    if (!$user) {
        throw new RuntimeException('Usuario no encontrado.');
    }
    if (($user['avatar_tipo'] ?? '') === 'custom') {
        delete_custom_avatar_file((string)($user['avatar_valor'] ?? ''));
    }

    $pdo = get_pdo();
    $stmt = $pdo->prepare('UPDATE usuarios SET avatar_tipo = :tipo, avatar_valor = NULL, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        'id' => $id,
        'tipo' => 'default',
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}
