

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