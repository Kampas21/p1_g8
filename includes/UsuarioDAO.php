<?php
declare(strict_types=1);

require_once __DIR__ . '/application.php';
require_once __DIR__ . '/../entities/usuario.php'; 
require_once __DIR__ . '/util.php';

class UsuarioDAO {
    
    // --- MÉTODOS DE UTILIDAD DE NEGOCIO (PREVIAMENTE EN util.php) ---

    public static function avatar_presets(): array {
        return [
            'preset_chef' => ['label' => 'Opcion 1', 'path' => RUTA_APP . '/img/avatares/cocinero.png'],
            'preset_waiter' => ['label' => 'Opcion 2', 'path' => RUTA_APP . '/img/avatares/camarero.png'],
            'preset_manager' => ['label' => 'Opcion 3', 'path' => RUTA_APP . '/img/avatares/gerente.png'],
        ];
    }

    public static function default_avatar(): string {
        return RUTA_APP . '/img/avatares/default.png';
    }

    public static function valid_roles(): array {
        return ['cliente', 'camarero', 'cocinero', 'gerente'];
    }

    public static function role_priority(string $role): int {
        return match ($role) {
            'cliente' => 1,
            'camarero' => 2,
            'cocinero' => 3,
            'gerente' => 4,
            default => 0,
        };
    }

    public static function role_label(string $role): string {
        return ucfirst($role);
    }

    public static function upload_avatar_from_request(string $fieldName = 'avatar_upload'): ?array {
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
            'value' => 'img/avatares/' . $filename,
        ];
    }

    public static function resolve_avatar_choice_from_request(?Usuario $currentUser = null, bool $isCreate = false): array {
        $mode = (string)($_POST['avatar_mode'] ?? ($isCreate ? 'default' : 'keep'));

        if (!$isCreate && $mode === 'keep') {
            return [
                // Y aquí llamamos a los métodos getter del objeto si existe
                'type' => $currentUser ? $currentUser->getAvatarTipo() : 'default',
                'value' => $currentUser ? $currentUser->getAvatarValor() : null,
            ];
        }

        if ($mode === 'default' || $mode === 'remove_custom') {
            return ['type' => 'default', 'value' => null];
        }

      if ($mode === 'preset') {

    $preset = (string)($_POST['avatar_preset'] ?? '');

    if ($preset === '') {
        return ['type' => 'default', 'value' => null];
    }

    $presets = self::avatar_presets();

    if (!isset($presets[$preset])) {
        throw new RuntimeException('Debes seleccionar un avatar predefinido válido.');
    }

    return ['type' => 'preset', 'value' => $preset];
}

        if ($mode === 'upload') {
            $uploaded = self::upload_avatar_from_request('avatar_upload');
            if ($uploaded === null) {
                throw new RuntimeException('Debes seleccionar un archivo para subir como avatar.');
            }
            return $uploaded;
        }

        throw new RuntimeException('Opción de avatar no válida.');
    }

    public static function delete_custom_avatar_file(string $relativePath): void {
        $relativePath = ltrim($relativePath, '/');
        $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }


    // --- MÉTODOS CRUD (PREVIAMENTE EN user_repo.php) ---

    // Función de ayuda para convertir la fila de la BBDD a Objeto
    private static function row_to_usuario(array $row): Usuario {
        $row['avatar_url'] = self::user_row_to_avatar_url($row);
        return new Usuario(
            (int)$row['id'],
            $row['username'],
            $row['email'],
            $row['nombre'],
            $row['apellidos'],
            $row['password_hash'],
            $row['rol'] ?? 'cliente',
            $row['avatar_tipo'] ?? 'default',
            $row['avatar_valor'] ?? null,
            $row['avatar_url'],
            (int)$row['activo'],
            $row['updated_at'] ?? '',
            (int)($row['bistrocoins'] ?? 0)
        );
    }

    private static function user_row_to_avatar_url(array $row): string {
        $tipo = (string)($row['avatar_tipo'] ?? 'default');
        $valor = (string)($row['avatar_valor'] ?? '');

        if ($tipo === 'custom' && $valor !== '') {
            return RUTA_APP . '/' . ltrim($valor, '/');
        }

        if ($tipo === 'preset') {
            $presets = self::avatar_presets();
            if (isset($presets[$valor])) {
                return $presets[$valor]['path'];
            }
        }

        return self::default_avatar();
    }

    public static function user_find_by_id(int $id, bool $includeInactive = true): ?Usuario {
        $conn = crearConexion();
        $sql = $includeInactive 
            ? "SELECT * FROM usuarios WHERE id = ? LIMIT 1" 
            : "SELECT * FROM usuarios WHERE id = ? AND activo = 1 LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();

        $stmt->close();
        $conn->close();

        return $row ? self::row_to_usuario($row) : null;
    }

    public static function user_find_by_username_or_email(string $login): ?Usuario {
        $conn = crearConexion();
        $login = trim($login);
        $sql = "SELECT * FROM usuarios WHERE activo = 1 AND (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)) LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        
        $stmt->close();
        $conn->close();

        return $row ? self::row_to_usuario($row) : null;
    }

    public static function user_list(array $opts = []): array {
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

       $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = self::row_to_usuario($row);
        }

        $result->free();
        $stmt->close();
        $conn->close();

        return $usuarios;
    }

    public static function user_validate_data(array $input, bool $isCreate, ?int $editingId = null, bool $allowRoleEdit = false): array {
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
        } elseif (!in_array($clean['rol'], self::valid_roles(), true)) {
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
            $result->free();
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
            $result->free();
            $stmt->close();
        }

        $conn->close();

        return [$clean, $errors];
    }

    public static function user_create(array $data, array $avatarChoice): int {
        $conn = crearConexion();

        $sql = "INSERT INTO usuarios
                (username, email, nombre, apellidos, password_hash, rol, avatar_tipo, avatar_valor, activo, created_at, updated_at, bistrocoins)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW(), 0)";

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

    public static function user_update(int $id, array $data, array $opts = []): void {
$conn = crearConexion();
        $existing = self::user_find_by_id($id);

        if (!$existing) {
            throw new RuntimeException('Usuario no encontrado.');
        }

        $set = [
            'username = ?',
            'email = ?',
            'nombre = ?',
            'apellidos = ?',
            'updated_at = NOW()'
        ];

        $params = [
            $data['username'],
            $data['email'],
            $data['nombre'],
            $data['apellidos']
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

            
            if ($existing->getAvatarTipo() === 'custom') {
                $old = $existing->getAvatarValor() ?? '';
                $new = $avatarChoice['value'] ?? '';

                if ($old && $old !== $new) {
                    self::delete_custom_avatar_file($old);
                }
            }
        }

        
        $sql = "UPDATE usuarios SET " . implode(', ', $set) . " WHERE id = ?";
        $params[] = $id;
        $types .= 'i';

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    public static function user_soft_delete(int $id): void {
        $conn = crearConexion();
        $user = self::user_find_by_id($id);

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

    public static function user_reactivate(int $id): void {
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

    public static function user_remove_custom_avatar(int $id): void {
        $user = self::user_find_by_id($id);
        if (!$user) {
            throw new RuntimeException('Usuario no encontrado.');
        }

        if ($user->getAvatarTipo() === 'custom') {
            self::delete_custom_avatar_file($user->getAvatarValor() ?? '');
        }

        $conn = crearConexion();
        $tipo = 'default';
        $sql = "UPDATE usuarios SET avatar_tipo = ?, avatar_valor = NULL, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $tipo, $id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    public static function getBistrocoinsByUserId(int $id): int {
        $conn = crearConexion();
        $stmt = $conn->prepare("SELECT bistrocoins FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $result->free();
        $stmt->close();
        $conn->close();
        return (int)($row['bistrocoins'] ?? 0);
    }

    public static function ajustarBistrocoins(int $id, int $delta): bool {
        $conn = crearConexion();
        $sql = "UPDATE usuarios SET bistrocoins = GREATEST(0, bistrocoins + ?), updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $delta, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }

    public static function setBistrocoins(int $id, int $coins): bool {
        $coins = max(0, $coins);
        $conn = crearConexion();
        $sql = "UPDATE usuarios SET bistrocoins = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $coins, $id);
        $ok = $stmt->execute();
        $stmt->close();
        $conn->close();
        return $ok;
    }
}
