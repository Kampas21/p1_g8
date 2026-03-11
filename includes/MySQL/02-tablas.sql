

/* Deshabilitar la revisión de las claves foráneas en phpMyAdmin */

USE `BistroFDI_G8`;

CREATE TABLE IF NOT EXISTS `categorias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100),
    `descripcion` TEXT,
    `imagen` VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS `productos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100),
    `descripcion` TEXT,
    `categoria_id` INT,
    `precio_base` DECIMAL(10,2),
    `iva` INT,
    `disponible` BOOLEAN,
    `ofertado` BOOLEAN,
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
);

CREATE TABLE IF NOT EXISTS `pedidos`(
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `numero_pedido` INT DEFAULT NULL,
    `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    `estado` ENUM('nuevo', 'recibido', 'en_preparacion', 'cocinando', 'listo_cocina', 'terminado', 'entregado', 'cancelado'),
    `tipo` ENUM('local', 'llevar'), 
    `metodo_pago` ENUM('tarjeta', 'camarero') DEFAULT NULL,
    `usuario_id` INT NOT NULL,
    `total` DECIMAL(10,2),
    `cocinero_id` INT DEFAULT NULL,

    -- Clave foránea hacia la tabla de usuarios para el cliente que hizo el pedido
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),

    -- El número de pedido es único para un día concreto
    UNIQUE (DATE(fecha_hora), numero_pedido),

    -- Clave foránea hacia la tabla de usuarios para el cocinero asignado (se rellena cuando pasa a cocinando)
    FOREIGN KEY (cocinero_id) REFERENCES usuarios(id)
);


CREATE TABLE IF NOT EXISTS `productos_en_pedido` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `pedido_id` INT NOT NULL,
    `producto_id` INT NOT NULL,

    `cantidad` INT NOT NULL DEFAULT 1,
    `precio_unitario` DECIMAL(10,2) NOT NULL,

    `estado` ENUM('pendiente', 'preparado') DEFAULT 'pendiente',

    -- para que si se borra un pedido o un producto, se borren también de esta tabla
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`),
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`),

    -- para que no se inserte el mismo producto dos veces en el mismo pedido, sino que incrementamos su cantidad
    UNIQUE (pedido_id, producto_id)
);

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