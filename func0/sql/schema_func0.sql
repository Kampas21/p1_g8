CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    nombre TEXT NOT NULL,
    apellidos TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    rol TEXT NOT NULL CHECK (rol IN ('cliente', 'camarero', 'cocinero', 'gerente')),
    avatar_tipo TEXT NOT NULL DEFAULT 'default' CHECK (avatar_tipo IN ('default', 'preset', 'custom')),
    avatar_valor TEXT NULL,
    activo INTEGER NOT NULL DEFAULT 1 CHECK (activo IN (0,1)),
    deleted_at TEXT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);
CREATE INDEX IF NOT EXISTS idx_usuarios_activo ON usuarios(activo);
