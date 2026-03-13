/* Deshabilitar la revisión de las claves foráneas en phpMyAdmin */
SET FOREIGN_KEY_CHECKS=0;

USE `BistroFDI_G8`;

CREATE TABLE IF NOT EXISTS `categorias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100),
    `descripcion` TEXT,
    `imagen` VARCHAR(255) DEFAULT NULL,
    `activa` TINYINT(1) NOT NULL DEFAULT 1
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
    `imagen` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`)
);

CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `nombre` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol` ENUM('cliente', 'camarero', 'cocinero', 'gerente') NOT NULL DEFAULT 'cliente',
    `avatar_tipo` ENUM('default', 'preset', 'custom') NOT NULL DEFAULT 'default',
    `avatar_valor` VARCHAR(255) DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `pedidos`(
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `numero_pedido` INT DEFAULT NULL,
    `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha` DATE GENERATED ALWAYS AS (DATE(`fecha_hora`)) STORED, 

    `estado` ENUM('nuevo', 'recibido', 'en_preparacion', 'cocinando', 'listo_cocina', 'terminado', 'entregado', 'cancelado'),
    `tipo` ENUM('local', 'llevar'), 
    `metodo_pago` ENUM('tarjeta', 'camarero') DEFAULT NULL,
    `usuario_id` INT NOT NULL,
    `total` DECIMAL(10,2),
    `cocinero_id` INT DEFAULT NULL,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    -- El número de pedido es único para un día concreto
    UNIQUE (`fecha`, `numero_pedido`),      
    FOREIGN KEY (cocinero_id) REFERENCES usuarios(id)
);


CREATE TABLE IF NOT EXISTS `productos_en_pedido` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `pedido_id` INT NOT NULL,
    `producto_id` INT NOT NULL,

    `cantidad` INT NOT NULL DEFAULT 1,
    `precio_unitario` DECIMAL(10,2) NOT NULL,

    `estado` ENUM('pendiente', 'preparado') DEFAULT 'pendiente',

    -- ON DELETE CASCADE para que si se borra un pedido o un producto, se borren también de esta tabla
    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE,

    -- Para que no se inserte el mismo producto dos veces en el mismo pedido
    UNIQUE (pedido_id, producto_id)
);

CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_usuarios_activo ON usuarios(activo);

SET FOREIGN_KEY_CHECKS=1;