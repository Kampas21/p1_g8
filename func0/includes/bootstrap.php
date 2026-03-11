<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/util.php';
require_once __DIR__ . '/user_repo.php';

function ensure_database_ready(): void {
    $dbFile = db_path();
    $needsInit = !is_file($dbFile) || filesize($dbFile) === 0;

    if ($needsInit) {
        $dir = dirname($dbFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = get_pdo();
        $schema = file_get_contents(__DIR__ . '/../sql/schema_func0.sql');
        if ($schema === false) {
            throw new RuntimeException('No se pudo leer el esquema de BD.');
        }
        $pdo->exec($schema);
        seed_demo_users($pdo);
        return;
    }

    $pdo = get_pdo();
    if (!db_table_exists($pdo, 'usuarios')) {
        $schema = file_get_contents(__DIR__ . '/../sql/schema_func0.sql');
        if ($schema === false) {
            throw new RuntimeException('No se pudo leer el esquema de BD.');
        }
        $pdo->exec($schema);
        seed_demo_users($pdo);
    }
}

function seed_demo_users(PDO $pdo): void {
    $exists = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    if ($exists > 0) {
        return;
    }

    $now = date('Y-m-d H:i:s');
    $users = [
        ['gerente', 'gerente@bistrofdi.local', 'Gema', 'Gerente', 'gerente123', 'gerente', 'preset', 'preset_manager'],
        ['cocinero', 'cocinero@bistrofdi.local', 'Carlos', 'Cocina', 'cocinero123', 'cocinero', 'preset', 'preset_chef'],
        ['camarero', 'camarero@bistrofdi.local', 'Clara', 'Camarera', 'camarero123', 'camarero', 'preset', 'preset_waiter'],
        ['cliente', 'cliente@bistrofdi.local', 'Lucía', 'Cliente', 'cliente123', 'cliente', 'default', null],
    ];

    $stmt = $pdo->prepare('
        INSERT INTO usuarios
            (username, email, nombre, apellidos, password_hash, rol, avatar_tipo, avatar_valor, activo, created_at, updated_at)
        VALUES
            (:username, :email, :nombre, :apellidos, :password_hash, :rol, :avatar_tipo, :avatar_valor, 1, :created_at, :updated_at)
    ');

    foreach ($users as [$username, $email, $nombre, $apellidos, $password, $rol, $avatarTipo, $avatarValor]) {
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => $rol,
            'avatar_tipo' => $avatarTipo,
            'avatar_valor' => $avatarValor,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

ensure_database_ready();

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';
