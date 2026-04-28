
/* Deshabilitar la revisión de las claves foráneas en phpMyAdmin */

USE `BistroFDI_G8`;

INSERT INTO `categorias` (`nombre`, `descripcion`, `imagen`) VALUES
('Platos Principales','Platos principales del restaurante, como pastas, carnes y pizzas','platos_principales.jpg'),
('Entrantes y Ensaladas','Entrantes ligeros y ensaladas frescas','entrantes_ensaladas.jpg'),
('Postres','Postres caseros y dulces para finalizar la comida','postres.jpg'),
('Bebidas','Bebidas frías y calientes, alcohólicas y no alcohólicas','bebidas.jpg');

INSERT INTO `productos` (`nombre`, `descripcion`, `categoria_id`, `precio_base`, `iva`, `disponible`, `ofertado`, `imagen`) VALUES
('Pizza Margherita','Pizza clásica con tomate, mozzarella y albahaca',1,8.50,10,1,1,'img/img_productos/pizza-margarita.jpg'),
('Spaghetti Carbonara','Pasta con salsa carbonara y panceta',1,9.75,10,1,0,'img/img_productos/1773418057_espaguetticarbonara.jpg'),
('Ensalada César','Lechuga, pollo, crutones y salsa César',2,7.00,10,1,1,'img/img_productos/1773420571_ensaladacesar.jpg'),
('Hamburguesa Gourmet','Hamburguesa de ternera con queso cheddar y bacon',1,11.50,10,1,0,'img/img_productos/1773419302_hamburguesagourmet.jpg'),
('Sopa de Tomate','Sopa casera de tomate y albahaca',2,5.00,10,1,0,'img/img_productos/1773420583_sopatomate.jpg'),
('Tarta de Queso','Tarta de queso con base de galleta y sirope de caramelo',3,4.50,10,1,1,'img/img_productos/tartaqueso.jpg'),
('Agua Mineral','Botella de agua mineral 500ml',4,1.50,10,1,0,'img/img_productos/aguamineral.jpg'),
('Refresco Cola','Bebida carbonatada de cola 330ml',4,2.00,10,1,1,'img/img_productos/refrescocola.jpg'),
('Café Expreso','Café negro intenso, 50ml',4,1.80,10,1,1,'img/img_productos/cafeexpreso.jpg'),
('Helado Vainilla','Cuenco de helado de vainilla',3,3.50,10,1,0,'img/img_productos/heladovainilla.jpg'),
('Pizza Pepperoni','Pizza con pepperoni y extra queso',1,9.50,10,1,1,'img/img_productos/1773419361_pizzapepperoni.jpg'),
('Lasagna Boloñesa','Lasaña de carne con bechamel',1,10.00,10,1,0,'img/img_productos/1773419413_lasanabolonesa.jpg'),
('Ensalada Caprese','Tomate, mozzarella y albahaca fresca',2,6.50,10,1,0,'img/img_productos/1773420610_ensaladacapresse.jpg'),
('Crema de Calabaza','Sopa suave de calabaza',2,5.50,10,1,0,'img/img_productos/cremacalabaza.jpg'),
('Brownie Chocolate','Brownie casero con nueces',3,4.00,10,1,1,'img/img_productos/browniechocolate.jpg'),
('Zumo Naranja','Zumo natural exprimido',4,2.50,10,1,0,'img/img_productos/zumonaranja.jpg'),
('Cerveza Rubia','Cerveza lager 330ml',4,2.80,10,1,0,'img/img_productos/cervezarubia.jpg'),
('Pizza Hawaiana','Pizza con jamón y piña',1,9.00,10,1,0,'img/img_productos/pizzahawaiana.jpg'),
('Ravioli Ricotta','Raviolis rellenos de ricotta y espinaca',1,9.75,10,1,0,'img/img_productos/1773419574_ravioliriccota.jpg'),
('Ensalada Mixta','Lechuga, tomate, esparragos, aceitunas, atun, cebolla y huevos',2,6.00,10,1,1,'img/img_productos/ensaladamixta.jpg'),
('Sopa de Lentejas','Sopa casera de lentejas con verduras',2,5.25,10,1,0,'img/img_productos/sopalentejas.jpg'),
('Tiramisú','Postre italiano con café y mascarpone',3,4.75,10,1,1,'img/img_productos/tiramisu.jpg'),
('Helado Chocolate','Cucurucho de helado de chocolate',3,3.50,10,1,0,'img/img_productos/heladochocolate.jpg'),
('Agua con Gas','Botella 500ml',4,1.70,10,1,0,'img/img_productos/aguacongas.jpg'),
('Refresco Limón','Bebida carbonatada de limón 330ml',4,2.00,10,1,0,'img/img_productos/refrescolimon.jpg'),
('Pollo al Horno','Pollo asado con hierbas',1,12.00,10,1,0,'img/img_productos/1773420043_polloalhorno.jpg'),
('Bistec de Ternera','Filete de ternera a la plancha',1,15.50,10,1,0,'img/img_productos/1773420053_bistecternera.jpg'),
('Ensalada Griega','Lechuga, tomate, pepino, aceitunas negras y feta',2,7.00,10,1,1,'img/img_productos/ensaladagriega.jpg'),
('Crema de Champiñones','Sopa cremosa de champiñones',2,5.50,10,1,0,'img/img_productos/cremachampinones.jpg'),
('Flan Casero','Flan de huevo con caramelo',3,3.75,10,1,0,'img/img_productos/flancasero.jpg'),
('Mousse Chocolate','Mousse ligera de chocolate negro',3,4.25,10,1,1,'img/img_productos/moussechocolate.jpg'),
('Zumo Manzana','Zumo natural de manzana',4,2.50,10,1,0,'img/img_productos/zumomanzana.jpg'),
('Cerveza Negra','Cerveza tipo stout 330ml',4,3.00,10,1,0,'img/img_productos/cervezanegra.jpg'),
('Pizza Vegetariana','Pizza con verduras asadas',1,9.25,10,1,1,'img/img_productos/1773420068_pizzavegetariana.jpg'),
('Espaguetis Pesto','Pasta con salsa pesto y piñones',1,9.50,10,1,0,'img/img_productos/espaguetispesto.jpg'),
('Ensalada de Quinoa','Quinoa, verduras y vinagreta',2,7.25,10,1,1,'img/img_productos/ensaladaquinoa.jpg'),
('Sopa Minestrone','Sopa italiana con verduras y pasta',2,5.75,10,1,0,'img/img_productos/sopaminestrone.jpg'),
('Cheesecake','Tarta de queso estilo americano',3,4.50,10,1,1,'img/img_productos/cheesecake.jpg'),
('Helado Fresa','Cucurucho de helado de fresa',3,3.50,10,1,0,'img/img_productos/heladofresa.jpg'),
('Agua Grande','Botella 1,5L',4,2.50,10,1,0,'img/img_productos/aguagrande.jpg'),
('Refresco Naranja','Bebida carbonatada de naranja 330ml',4,2.00,10,1,0,'img/img_productos/refresconaranja.jpg'),
('Pizza Cuatro Quesos','Pizza con mezcla de cuatro quesos',1,10.00,10,1,1,'img/img_productos/pizzacuatroquesos.jpg'),
('Tagliatelle Boloñesa','Pasta fresca con salsa boloñesa',1,9.75,10,1,0,'img/img_productos/1773420127_tagliatellebolonesa.jpg'),
('Ensalada Waldorf','Lechuga, manzana, nueces y mayonesa',2,7.25,10,1,1,'img/img_productos/ensaladawaldorf.jpg'),
('Sopa de Verduras','Sopa casera con verduras frescas',2,5.50,10,1,0,'img/img_productos/sopaverduras.jpg'),
('Brownie Caramelo','Brownie con caramelo salado',3,4.00,10,1,1,'img/img_productos/browniecaramelo.jpg'),
('Helado Limón','Cucurucho de helado de limón',3,3.50,10,1,0,'img/img_productos/heladolimon.jpg'),
('Zumo Piña','Zumo natural de piña',4,2.50,10,1,0,'img/img_productos/zumopina.jpg'),
('Cerveza Roja','Cerveza ale 330ml',4,3.00,10,1,0,'img/img_productos/cervezaroja.jpg');


INSERT INTO `usuarios`(`username`, `email`, `nombre`, `apellidos`, `password_hash`, `rol`, `avatar_tipo`, `avatar_valor`, `activo`, `deleted_at`, `created_at`, `updated_at`) VALUES
('gerente','gerente@bistrofdi.local','Gema','García','$2y$10$I2vqnj3l34w4TkDp2vhwcO3nB2GvNN8.CtzPg1aoW1QsGTAalMYyy','gerente','preset','preset_manager',1,NULL,NOW(),NOW()),
('cocinero','cocinero@bistrofdi.local','Carlos','Lucas','$2y$10$Utc.tEtSUnEs3K30JRuOSOPBrXLHFsL/YsyVaIbfcHePq7sFUqRYy','cocinero','preset','preset_chef',1,NULL,NOW(),NOW()),
('camarero','camarero@bistrofdi.local','Clara','Gómez','$2y$10$R5/GSm23GKVSrK82tILtzeoh7HVuIn2tRWnODNjKTnGnbsagFBPDe','camarero','preset','preset_waiter',1,NULL,NOW(),NOW()),
('cliente','cliente@bistrofdi.local','Lucía','Lopez','$2y$10$1PE57AaW9hYc45FOd0RA/ebfNdQ4vaOsxRGO3HRJvHiLryxt8I2b.','cliente','default',NULL,1,NULL,NOW(),NOW());


INSERT INTO `ofertas` (`nombre`, `descripcion`, `fecha_inicio`, `fecha_fin`, `descuento`) VALUES
('Desayuno Simple', 'Café + tostada (simulado con ensalada básica)', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 20.00),

('Menú Italiano', 'Pizza + bebida con descuento', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 15.00),

('Menú Burger', 'Hamburguesa + refresco', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 18.00),

('Postre + Café', 'Postre con café a precio reducido', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 25.00),

('Menú Saludable', 'Ensalada + zumo natural', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 12.00);


INSERT INTO `oferta_productos` (`oferta_id`, `producto_id`, `cantidad`) VALUES
(1, 9, 1),
(1, 20, 1);

INSERT INTO `oferta_productos` (`oferta_id`, `producto_id`, `cantidad`) VALUES
(2, 1, 1),
(2, 8, 1);

INSERT INTO `oferta_productos` (`oferta_id`, `producto_id`, `cantidad`) VALUES
(3, 4, 1),
(3, 8, 1);

INSERT INTO `oferta_productos` (`oferta_id`, `producto_id`, `cantidad`) VALUES
(4, 6, 1),
(4, 9, 1);

INSERT INTO `oferta_productos` (`oferta_id`, `producto_id`, `cantidad`) VALUES
(5, 3, 1),
(5, 16, 1);
UPDATE productos SET se_cocina = 0 WHERE categoria_id = 4;
