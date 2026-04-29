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
    `se_cocina` BOOLEAN DEFAULT 1,
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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `bistrocoins` INT NOT NULL DEFAULT 0
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

    `total_sin_descuentos` DECIMAL(10,2) DEFAULT 0,
    `total_descuento` DECIMAL(10,2) DEFAULT 0,
    
    `total` DECIMAL(10,2) GENERATED ALWAYS AS (total_sin_descuentos - total_descuento) STORED,
    `bistrocoins_generados` INT NOT NULL DEFAULT 0,
    `bistrocoins_gastados` INT NOT NULL DEFAULT 0,
    `bistrocoins_liquidados` TINYINT(1) NOT NULL DEFAULT 0,
    `cocinero_id` INT DEFAULT NULL,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE (`fecha`, `numero_pedido`),      
    FOREIGN KEY (cocinero_id) REFERENCES usuarios(id)
);


CREATE TABLE IF NOT EXISTS `productos_en_pedido` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `pedido_id` INT NOT NULL,
    `producto_id` INT NOT NULL,

    `cantidad` INT NOT NULL DEFAULT 1,
    `precio_unitario` DECIMAL(10,2) NOT NULL,

    `estado` ENUM('pendiente', 'preparado', 'terminado') DEFAULT 'pendiente',
    `es_recompensa` TINYINT(1) NOT NULL DEFAULT 0,
    `bistrocoins_unitarios` INT NOT NULL DEFAULT 0,

    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE,

    UNIQUE (pedido_id, producto_id)
);

CREATE TABLE IF NOT EXISTS `recompensas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `producto_id` INT NOT NULL,
    `bistrocoins` INT NOT NULL,
    `activa` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE,
    UNIQUE (`producto_id`)
);

/* =========================
   TABLAS DE OFERTAS
   ========================= */

CREATE TABLE IF NOT EXISTS `ofertas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    
    `fecha_inicio` DATETIME NOT NULL,
    `fecha_fin` DATETIME NOT NULL,

    `descuento` DECIMAL(5,2) NOT NULL, -- ej: 21.50

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `oferta_productos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `oferta_id` INT NOT NULL,
    `producto_id` INT NOT NULL,

    `cantidad` INT NOT NULL DEFAULT 1,

    FOREIGN KEY (`oferta_id`) REFERENCES `ofertas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE,

    UNIQUE (`oferta_id`, `producto_id`)
);

CREATE TABLE IF NOT EXISTS `ofertas_en_pedido` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    `pedido_id` INT NOT NULL,
    `oferta_id` INT NOT NULL,

    `veces_aplicada` INT NOT NULL DEFAULT 1,

    `descuento_total` DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (`pedido_id`) REFERENCES `pedidos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`oferta_id`) REFERENCES `ofertas`(`id`)
);



SET FOREIGN_KEY_CHECKS=1;