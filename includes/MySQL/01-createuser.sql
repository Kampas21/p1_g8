CREATE USER IF NOT EXISTS 'bistro_user'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON `BistroFDI_G8`.* TO 'bistro_user'@'localhost';
FLUSH PRIVILEGES;

CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);
CREATE INDEX IF NOT EXISTS idx_usuarios_activo ON usuarios(activo);