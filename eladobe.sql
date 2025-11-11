-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 11-11-2025 a las 03:27:00
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `eladobe`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_permiso`
--

DROP TABLE IF EXISTS `asignacion_permiso`;
CREATE TABLE IF NOT EXISTS `asignacion_permiso` (
  `id_asignacion` int NOT NULL AUTO_INCREMENT,
  `id_rol` int DEFAULT NULL,
  `id_permiso` int DEFAULT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `id_rol` (`id_rol`),
  KEY `id_permiso` (`id_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_movimientos`
--

DROP TABLE IF EXISTS `bitacora_movimientos`;
CREATE TABLE IF NOT EXISTS `bitacora_movimientos` (
  `id_movimiento` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `modulo` varchar(100) DEFAULT NULL,
  `accion` varchar(255) DEFAULT NULL,
  `ip_usuario` varchar(45) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_movimiento`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
(1, 'Desayunos'),
(2, 'Entradas'),
(3, 'Almuerzos y Cenas'),
(4, 'Postres'),
(5, 'Bebidas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comestibles`
--

DROP TABLE IF EXISTS `comestibles`;
CREATE TABLE IF NOT EXISTS `comestibles` (
  `id_comestible` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `cantidad` int DEFAULT '0',
  `stock` int DEFAULT '0',
  `stock_minimo` int DEFAULT '0',
  `fecha_agregado` date DEFAULT (curdate()),
  `ultima_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comestible`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `comestibles`
--

INSERT INTO `comestibles` (`id_comestible`, `id_producto`, `categoria`, `cantidad`, `stock`, `stock_minimo`, `fecha_agregado`, `ultima_actualizacion`) VALUES
(1, 1, 'Verduras', 0, 10, 2, '2025-11-10', '2025-11-11 03:17:38'),
(2, 2, 'Carnes', 0, 15, 5, '2025-11-10', '2025-11-11 03:17:38'),
(3, 3, 'Carnes', 0, 20, 8, '2025-11-10', '2025-11-11 03:17:38'),
(4, 4, 'Granos', 0, 30, 10, '2025-11-10', '2025-11-11 03:17:38'),
(5, 5, 'Granos', 0, 25, 8, '2025-11-10', '2025-11-11 03:17:38'),
(6, 6, 'Verduras', 0, 15, 5, '2025-11-10', '2025-11-11 03:17:38'),
(7, 7, 'Verduras', 0, 12, 4, '2025-11-10', '2025-11-11 03:17:38'),
(8, 8, 'Frutas', 0, 18, 6, '2025-11-10', '2025-11-11 03:17:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

DROP TABLE IF EXISTS `compras`;
CREATE TABLE IF NOT EXISTS `compras` (
  `id_compra` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `fecha` date NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Tarjeta','Cheque') DEFAULT 'Efectivo',
  `numero_comprobante` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_compra`),
  KEY `id_proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_separadas`
--

DROP TABLE IF EXISTS `cuentas_separadas`;
CREATE TABLE IF NOT EXISTS `cuentas_separadas` (
  `id_cuenta` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int NOT NULL,
  `numero_cuenta` int NOT NULL,
  `cliente_nombre` varchar(255) DEFAULT NULL,
  `cliente_nit` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT '0.00',
  `iva` decimal(10,2) DEFAULT '0.00',
  `propina` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) DEFAULT '0.00',
  `metodo_pago` enum('efectivo','tarjeta','transferencia','mixto') DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cuenta`),
  KEY `id_pedido` (`id_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compras`
--

DROP TABLE IF EXISTS `detalle_compras`;
CREATE TABLE IF NOT EXISTS `detalle_compras` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_compra` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_compra` (`id_compra`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Disparadores `detalle_compras`
--
DROP TRIGGER IF EXISTS `trg_actualizar_stock`;
DELIMITER $$
CREATE TRIGGER `trg_actualizar_stock` AFTER INSERT ON `detalle_compras` FOR EACH ROW BEGIN
    DECLARE tipo_prod ENUM('comestible','mobiliario');
    SELECT tipo INTO tipo_prod FROM productos WHERE id_producto = NEW.id_producto;

    IF tipo_prod = 'comestible' THEN
        UPDATE comestibles 
        SET cantidad = cantidad + NEW.cantidad, stock = stock + NEW.cantidad
        WHERE id_producto = NEW.id_producto;
    ELSE
        UPDATE mobiliario_equipo 
        SET cantidad = cantidad + NEW.cantidad, stock = stock + NEW.cantidad
        WHERE id_producto = NEW.id_producto;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_cuenta`
--

DROP TABLE IF EXISTS `detalle_cuenta`;
CREATE TABLE IF NOT EXISTS `detalle_cuenta` (
  `id_detalle_cuenta` int NOT NULL AUTO_INCREMENT,
  `id_cuenta` int NOT NULL,
  `id_detalle_pedido` int NOT NULL,
  `cantidad_asignada` int NOT NULL,
  PRIMARY KEY (`id_detalle_cuenta`),
  KEY `id_cuenta` (`id_cuenta`),
  KEY `id_detalle_pedido` (`id_detalle_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
CREATE TABLE IF NOT EXISTS `detalle_pedido` (
  `id_detalle_pedido` int NOT NULL AUTO_INCREMENT,
  `id_pedido` int NOT NULL,
  `id_menu` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS ((`cantidad` * `precio_unitario`)) STORED,
  PRIMARY KEY (`id_detalle_pedido`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_menu` (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_receta`
--

DROP TABLE IF EXISTS `detalle_receta`;
CREATE TABLE IF NOT EXISTS `detalle_receta` (
  `id_detalle_receta` int NOT NULL AUTO_INCREMENT,
  `id_receta` int NOT NULL,
  `id_comestible` int NOT NULL,
  `cantidad_usada` decimal(10,2) NOT NULL,
  `unidad` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_detalle_receta`),
  KEY `id_receta` (`id_receta`),
  KEY `id_comestible` (`id_comestible`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `detalle_receta`
--

INSERT INTO `detalle_receta` (`id_detalle_receta`, `id_receta`, `id_comestible`, `cantidad_usada`, `unidad`) VALUES
(1, 1, 1, 0.20, 'kg'),
(2, 1, 2, 0.15, 'kg'),
(3, 2, 2, 0.25, 'kg'),
(4, 3, 3, 0.30, 'kg'),
(5, 4, 4, 0.18, 'kg'),
(6, 5, 5, 0.22, 'kg'),
(7, 6, 8, 0.50, 'kg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

DROP TABLE IF EXISTS `detalle_venta`;
CREATE TABLE IF NOT EXISTS `detalle_venta` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_menu` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS ((`cantidad` * `precio_unitario`)) STORED,
  PRIMARY KEY (`id_detalle`),
  KEY `id_venta` (`id_venta`),
  KEY `id_menu` (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

DROP TABLE IF EXISTS `facturas`;
CREATE TABLE IF NOT EXISTS `facturas` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_pedido` int NOT NULL,
  `numero_factura` varchar(20) DEFAULT NULL,
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `iva` decimal(10,2) DEFAULT '0.00',
  `propina` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) DEFAULT NULL,
  `cliente_nombre` varchar(255) DEFAULT NULL,
  `cliente_nit` varchar(20) DEFAULT NULL,
  `direccion` text,
  `metodo_pago` enum('efectivo','tarjeta','transferencia','mixto') DEFAULT NULL,
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `numero_factura` (`numero_factura`),
  KEY `id_venta` (`id_venta`),
  KEY `id_pedido` (`id_pedido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE IF NOT EXISTS `horarios` (
  `id_horario` int NOT NULL AUTO_INCREMENT,
  `nombre` enum('desayuno','almuerzo','cena') COLLATE utf8mb4_unicode_ci NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  PRIMARY KEY (`id_horario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horario`, `nombre`, `hora_inicio`, `hora_fin`) VALUES
(1, 'desayuno', '07:00:00', '11:00:00'),
(2, 'almuerzo', '11:00:00', '17:00:00'),
(3, 'cena', '17:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hreservaciones`
--

DROP TABLE IF EXISTS `hreservaciones`;
CREATE TABLE IF NOT EXISTS `hreservaciones` (
  `id_reservacion` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_mesa` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `id_horario` int NOT NULL,
  `estado` enum('activa','en_espera','cancelada') DEFAULT 'activa',
  `id_salon` int DEFAULT NULL,
  PRIMARY KEY (`id_reservacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_horario` (`id_horario`),
  KEY `fk_reservacion_salon` (`id_salon`),
  KEY `fk_reservacion_mesa` (`id_mesa`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `hreservaciones`
--

INSERT INTO `hreservaciones` (`id_reservacion`, `id_usuario`, `id_mesa`, `fecha`, `id_horario`, `estado`, `id_salon`) VALUES
(1, 4, NULL, '2025-11-22', 1, 'activa', 2),
(2, 4, 1, '2025-11-22', 2, 'activa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu`
--

DROP TABLE IF EXISTS `menu`;
CREATE TABLE IF NOT EXISTS `menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `id_categoria` int NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_menu`),
  KEY `id_categoria` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `menu`
--

INSERT INTO `menu` (`id_menu`, `nombre`, `descripcion`, `precio`, `id_categoria`, `activo`) VALUES
(1, 'Típico', 'Huevos al gusto, acompañados de plátanos fritos, frijoles, queso, crema y longaniza', 70.00, 1, 1),
(2, 'Huevos Ixín', 'Huevos estrellados servidos sobre tortilla frita y salsa verde, plátanos fritos, frijoles colados, queso, crema y longaniza', 75.00, 1, 1),
(3, 'Desayuno de Mi Pueblo', 'Omelette relleno de chorizo ahumado con sofrito de tomate, cebolla y chile pimiento acompañado de frijol parado y tortilla con queso', 80.00, 1, 1),
(4, 'Desayuno Paxot', 'Huevos estrellados cocinados al comal bañados en salsa Subanik, chorizo, tortilla frita y queso criollo', 85.00, 1, 1),
(5, 'Desayuno Granjero', 'Huevos estrellados montados sobre carne de res, frijoles y tortilla con queso derretido', 95.00, 1, 1),
(6, 'Huevos Rancheros', 'Huevos estrellados bañados en salsa ranchera acompañada de frijol, plátanos fritos, queso, crema y tortilla con queso derretido y longaniza', 80.00, 1, 1),
(7, 'Omelette de Claras', 'Relleno de champiñón, espinaca, queso mozzarella y chile pimiento, acompañado de plátanos cocidos y bowl de fruta', 75.00, 1, 1),
(8, 'Desayuno Oriente', 'Omelette relleno de loroco, chile pimiento, cebolla y jamón', 80.00, 1, 1),
(9, 'El Adobe', 'Huevos al gusto, queso, crema, plátanos, salsa y 4oz de carne adobada', 90.00, 1, 1),
(10, 'Omelette Tradicional', 'Relleno de jamón, champiñones y queso', 80.00, 1, 1),
(11, 'Tamales', 'Receta tradicional sellada en hoja de mazán', 45.00, 1, 1),
(12, 'Caldo de Huevo', '', 60.00, 1, 1),
(13, 'Moshito', 'Atol acompañado con plátanos cocidos', 40.00, 1, 1),
(14, 'Panqueques', 'Mezcla exclusiva de El Adobe, acompañados con miel de abeja o maple', 70.00, 1, 1),
(15, 'Tostadas a la Francesa', 'Con pan brioche', 75.00, 1, 1),
(16, 'Tazón de Frutas', 'Yogurt acompañado de fresas, arándanos, kiwi, pepitoria, nueces nogal, pasas, chan y coco rallado', 75.00, 1, 1),
(17, 'Fruta de la Estación', 'Festín de frutas frescas, acompañadas con yogurt, granola y miel', 75.00, 1, 1),
(18, 'Tostadas Variadas (4)', 'De chojín, buche y salpicón', 60.00, 2, 1),
(19, 'Tostadas Chapinas (4)', 'Guacamol, frijol, salsa', 50.00, 2, 1),
(20, 'Tortillas con Queso Derretido (2)', '', 35.00, 2, 1),
(21, 'Tortillas con Longaniza o Chorizo Ahumado (2)', 'Cebollín, guacamol y chirmol', 45.00, 2, 1),
(22, 'Tortillas con Morcilla (4)', '', 55.00, 2, 1),
(23, 'Garnachas', '', 40.00, 2, 1),
(24, 'Chuchitos (2)', '', 30.00, 2, 1),
(25, 'Tamalitos de Chipilín (3)', '', 35.00, 2, 1),
(26, 'Enchilada', 'Tostada con curtido de remolacha, zanahoria y ejote', 45.00, 2, 1),
(27, 'Chiles Rellenos (2)', '', 65.00, 2, 1),
(28, 'Mixtas de Salchicha o Chorizo (4)', 'Tortillas con guacamol, repollo, salchicha, ketchup y mayonesa', 50.00, 2, 1),
(29, 'Elotes Locos (4)', '', 40.00, 2, 1),
(30, 'Crema de Frijol', '', 35.00, 2, 1),
(31, 'Mixtas de Chorizo Ahumado', '', 55.00, 2, 1),
(32, 'Jocón de Pollo o Res', 'Plato de occidente, recado verde a base de miltomates y cilantro servido con arroz blanco', 100.00, 3, 1),
(33, 'Jocón de Pechuga de Pollo', 'Plato de occidente, recado verde a base de miltomates y cilantro servido con arroz blanco', 110.00, 3, 1),
(34, 'Jocón de Gallina Criolla', 'Plato de occidente, recado verde a base de miltomates y cilantro servido con arroz blanco', 130.00, 3, 1),
(35, 'Kaqik', 'Sopa ceremonial de Cobán con variedad de especias de la región', 200.00, 3, 1),
(36, 'Pepián de Pollo o Res', 'Plato típico de occidente. Recado de tomate y pepitoria servido con arroz típico', 100.00, 3, 1),
(37, 'Pepián de Pechuga de Pollo', 'Plato típico de occidente. Recado de tomate y pepitoria servido con arroz típico', 110.00, 3, 1),
(38, 'Pepián de Gallina Criolla', 'Plato típico de occidente. Recado de tomate y pepitoria servido con arroz típico', 130.00, 3, 1),
(39, 'Pinol de Gallina Criolla', 'Plato prehispánico originario de San Juan Sacatepéquez, a base de maíz dorado', 130.00, 3, 1),
(40, 'Caldo de Res', 'Cocido de res, zanahoria, papa, güisquil, elote, repollo y güicoy. Acompañado de tortillas, arroz y aguacate', 125.00, 3, 1),
(41, 'Caldo de Gallina Criolla', 'Gallina criolla cocida o asada, acompañado de tortillas, verduras, arroz y aguacate', 150.00, 3, 1),
(42, 'Tapado', 'Platillo típico de Livingston, Izabal', 200.00, 3, 1),
(43, 'Caldo de Mariscos', '', 190.00, 3, 1),
(44, 'Carne Adobada', 'Servida con puré de papa, ensalada y porción de aguacate', 120.00, 3, 1),
(45, 'Camarones Empanizados o al Ajillo', 'Acompañados de verduras salteadas y arroz', 180.00, 3, 1),
(46, 'Camarones Utzaj', '', 170.00, 3, 1),
(47, 'Ceviche de Camarón', 'Tomate picado, cebolla, hierbabuena, limón y camarón', 90.00, 3, 1),
(48, 'Mojarra', 'Servida con ensalada y papas asadas', 140.00, 3, 1),
(49, 'Pollo o Gallina en Crema', 'Bañado en salsa de crema y loroco, acompañado de arroz y elote dulce', 110.00, 3, 1),
(50, 'Pechuga en Pimientos', 'Pechuga de pollo frita bañada en salsa de pimientos, acompañada con cacerola de güicoy con queso y ensalada escabeche', 130.00, 3, 1),
(51, 'Pechuga a la Parrilla', 'Filete a la parrilla, acompañado con mix de lechuga y verduras cocidas', 120.00, 3, 1),
(52, 'Ojer Kutun', 'Comida Ancestral representada en cinco platillos de la gastronomía maya', 120.00, 3, 1),
(53, 'Subanik', 'Platillo ceremonial de San Martín Jilotepaque, preparado con combinación de carnes y chiles, cocido a fuego lento dentro de hojas de moxán', 110.00, 3, 1),
(54, 'Revolcado', 'Recado tradicional de Sacatepéquez, hecho a base de cabeza y vísceras de cerdo', 100.00, 3, 1),
(55, 'Frijol Colorado', 'Con castilla de cerdo y chorizo ahumado', 110.00, 3, 1),
(56, 'Frijol Blanco con Espinazo', '', 110.00, 3, 1),
(57, 'Hilachas', '', 110.00, 3, 1),
(58, 'Panza de Res', '', 100.00, 3, 1),
(59, 'Lengua Guisada', '', 100.00, 3, 1),
(60, 'Costilla en Barbacoa', 'Acompañada de puré de papa y elote dulce', 110.00, 3, 1),
(61, 'Fajitas de Res o Pollo', 'Con chile pimiento y cebolla acompañadas de guacamol y arroz', 110.00, 3, 1),
(62, 'Pinchos', 'Pinchos de lomito, piña, chile pimiento y cebolla a la parrilla, servidos en cama de arroz y ensalada de la casa', 130.00, 3, 1),
(63, 'Pinchos Mar y Tierra', '', 170.00, 3, 1),
(64, 'Puñazo o Lomito', 'Carne de 8oz a la parrilla acompañada de cebollines, papa asada, guacamol y frijol', 150.00, 3, 1),
(65, 'Entraña 12oz', 'Servida con guacamol, cebollines asados, frijol y papa asada', 200.00, 3, 1),
(66, 'Entraña 8oz', 'Servida con guacamol, cebollines asados, frijol y papa asada', 170.00, 3, 1),
(67, 'Chorizo o Longaniza 1lb', 'Frijoles volteados, guacamol y tortillas', 120.00, 3, 1),
(68, 'Chorizo o Longaniza 1/2lb', 'Frijoles volteados, guacamol y tortillas', 80.00, 3, 1),
(69, 'Chorizo Ahumado 1lb', 'Frijoles volteados, guacamol y tortillas', 130.00, 3, 1),
(70, 'Chorizo Ahumado 1/2lb', 'Frijoles volteados, guacamol y tortillas', 90.00, 3, 1),
(71, 'Parrillada Especial', '12 lb de lomito, 1/2 lb de puñazo, 1/2 lb de chorizo, porción de frijoles volteados, guacamol, cebollines y papa asada', 295.00, 3, 1),
(72, 'Parrillada Familiar', '1lb de puñazo, 1lb de lomito, 1lb de chorizo, 3 piezas de pollo asado, cebollines, frijoles volteados, guacamol y papas asadas', 500.00, 3, 1),
(73, 'Plato Vegetariano', 'Zuchinni, berenjena, chile pimiento, cebolla, tomate y champiñones', 80.00, 3, 1),
(74, 'Ensalada César', 'Lechuga, tomate, aguacate y pechuga de pollo a la parrilla', 80.00, 3, 1),
(75, 'Ensalada de Quinoa', '', 80.00, 3, 1),
(76, 'Hamburguesa 1/4 lb', 'Carne a la parrilla, servida con lechuga y tomate, acompañada de papas fritas', 55.00, 3, 1),
(77, 'Hamburguesita', 'Carne a la parrilla, servida con lechuga y tomate, acompañada de papas fritas', 45.00, 3, 1),
(78, 'Deditos de Pollo', 'Fritos, servidos con salsa dulce y papas fritas', 50.00, 3, 1),
(79, 'Queso-Hamburguesa 1/4 lb', 'Carne con queso cheddar derretido, servida con lechuga y tomate, acompañada de papas fritas', 60.00, 3, 1),
(80, 'Queso-Hamburguesita', 'Carne con queso cheddar derretido, servida con lechuga y tomate, acompañada de papas fritas', 50.00, 3, 1),
(81, 'Shuauito', 'Con salchicha, guacamol y repollo acompañado con papas fritas', 50.00, 3, 1),
(82, 'Pollo Frito', 'Acompañado con papas fritas', 50.00, 3, 1),
(83, 'Chancleta Individual', 'Postre de güisquil', 35.00, 4, 1),
(84, 'Chancleta', 'Postre de güisquil', 40.00, 4, 1),
(85, 'Male de Plátano', '', 35.00, 4, 1),
(86, 'Tartaleta de Fruta', '', 35.00, 4, 1),
(87, 'Flan Antigueño', '', 40.00, 4, 1),
(88, 'Rellenitos (2)', '', 30.00, 4, 1),
(89, 'Tres Leches', '', 40.00, 4, 1),
(90, 'Buñuelos (3)', '', 30.00, 4, 1),
(91, 'Torreja (1)', '', 30.00, 4, 1),
(92, 'Tarta de Elote', '', 30.00, 4, 1),
(93, 'Jugo de Naranja Natural 12oz', 'Jugo natural recién exprimido', 25.00, 5, 1),
(94, 'Jugo de Naranja Natural 16oz', 'Jugo natural recién exprimido', 30.00, 5, 1),
(95, 'Fruit Punch', 'Combinación de jugos naturales y granadina', 25.00, 5, 1),
(96, 'Licuado de Frutas Regular', 'Papaya, piña, melón, sandía, banana, mora, fresa o mixta', 25.00, 5, 1),
(97, 'Licuado de Frutas con Leche', 'Papaya, piña, melón, sandía, banana, mora, fresa o mixta', 30.00, 5, 1),
(98, 'Licuado de Frutas con Yogurt', 'Papaya, piña, melón, sandía, banana, mora, fresa o mixta', 35.00, 5, 1),
(99, 'Licuado Nutritivo 1', 'Apio, zanahoria, elote dulce y jugo de naranja', 25.00, 5, 1),
(100, 'Licuado Nutritivo 2', 'Pepino, jugo de naranja, apio, espinaca y piña', 25.00, 5, 1),
(101, 'Licuado Nutritivo 3', 'Aguacate, chan, piña, espinaca y jugo de naranja', 25.00, 5, 1),
(102, 'Licuado Nutritivo 4', 'Kiwi, jugo de naranja, apio y piña', 25.00, 5, 1),
(103, 'Limonada o Naranjada Regular', 'Natural', 25.00, 5, 1),
(104, 'Limonada o Naranjada Piche', 'Natural', 90.00, 5, 1),
(105, 'Repitaza Simple Regular', '', 25.00, 5, 1),
(106, 'Repitaza Simple Piche', '', 90.00, 5, 1),
(107, 'Botella de Agua', '', 15.00, 5, 1),
(108, 'Cimarrona', 'Agua mineral preparada con sal y limón', 25.00, 5, 1),
(109, 'Santa Delfina', '', 28.00, 5, 1),
(110, 'Té Frío', '', 25.00, 5, 1),
(111, 'Jugo de Tomate Preparado Natural', '', 25.00, 5, 1),
(112, 'Refresco Natural Regular', 'Horchata, jamaica, tamarindo, carambola, pepita o tiste', 25.00, 5, 1),
(113, 'Refresco Natural Piche', 'Horchata, jamaica, tamarindo, carambola, pepita o tiste', 90.00, 5, 1),
(114, 'Gaseosa', 'Pepsi, 7Up, Pepsi Zero Azúcar, 7Up Light', 18.00, 5, 1),
(115, 'Smoothie', 'Tamarindo, berries, tropical, mango, piña colada y carambola', 25.00, 5, 1),
(116, 'Michelada', 'Especial de El Adobe con jugo natural de la casa', 40.00, 5, 1),
(117, 'Michelada con Cerveza Premium', '', 44.00, 5, 1),
(118, 'Arroz en Leche', '', 20.00, 5, 1),
(119, 'Espresso Regular', '', 12.00, 5, 1),
(120, 'Espresso Doble', '', 16.00, 5, 1),
(121, 'Cortado Regular', '', 14.00, 5, 1),
(122, 'Cortado Doble', '', 18.00, 5, 1),
(123, 'Macchiato', '', 15.00, 5, 1),
(124, 'Americano', '', 17.00, 5, 1),
(125, 'Cappuccino', '', 20.00, 5, 1),
(126, 'Latte', '', 20.00, 5, 1),
(127, 'Latte Saborizado', '', 25.00, 5, 1),
(128, 'Mocaccino', '', 25.00, 5, 1),
(129, 'Moka Artesanal', '', 25.00, 5, 1),
(130, 'Latte Frío', '', 23.00, 5, 1),
(131, 'Té', '', 15.00, 5, 1),
(132, 'Té Chai', '', 25.00, 5, 1),
(133, 'Té Chai con Leche', '', 30.00, 5, 1),
(134, 'Chocolate Regular', '', 20.00, 5, 1),
(135, 'Chocolate Doble', '', 23.00, 5, 1),
(136, 'Té Matcha Regular', '', 26.00, 5, 1),
(137, 'Té Matcha Doble', '', 30.00, 5, 1),
(138, 'Frappuccino Regular', '', 28.00, 5, 1),
(139, 'Frappuccino Doble', '', 32.00, 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

DROP TABLE IF EXISTS `mesas`;
CREATE TABLE IF NOT EXISTS `mesas` (
  `id_mesa` int NOT NULL AUTO_INCREMENT,
  `numero` int NOT NULL,
  `capacidad` int DEFAULT '4',
  `estado` enum('libre','ocupada','reservada') DEFAULT 'libre',
  `id_mesero_asignado` int DEFAULT NULL,
  `id_salon` int DEFAULT NULL,
  PRIMARY KEY (`id_mesa`),
  UNIQUE KEY `numero` (`numero`),
  KEY `id_mesero_asignado` (`id_mesero_asignado`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id_mesa`, `numero`, `capacidad`, `estado`, `id_mesero_asignado`, `id_salon`) VALUES
(1, 1, 4, 'libre', 2, 1),
(2, 2, 6, 'libre', 2, 1),
(3, 3, 2, 'libre', 2, 2),
(4, 4, 4, 'libre', 2, 2),
(5, 5, 8, 'libre', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mobiliario_equipo`
--

DROP TABLE IF EXISTS `mobiliario_equipo`;
CREATE TABLE IF NOT EXISTS `mobiliario_equipo` (
  `id_mobiliario` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `cantidad` int DEFAULT '0',
  `stock` int DEFAULT '0',
  `stock_minimo` int DEFAULT '0',
  `fecha_agregado` date DEFAULT (curdate()),
  `ultima_actualizacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mobiliario`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id_pedido` int NOT NULL AUTO_INCREMENT,
  `id_mesa` int NOT NULL,
  `id_usuario` int NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','enviado','preparando','listo','entregado','finalizado','facturado','cancelado') DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT '0.00',
  `fecha_envio` datetime DEFAULT NULL,
  `fecha_preparacion` datetime DEFAULT NULL,
  `fecha_listo` datetime DEFAULT NULL,
  `fecha_entregado` datetime DEFAULT NULL,
  `fecha_finalizado` datetime DEFAULT NULL,
  `fecha_facturado` datetime DEFAULT NULL,
  `fecha_cancelado` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pedido`),
  KEY `id_mesa` (`id_mesa`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

DROP TABLE IF EXISTS `permisos`;
CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` int NOT NULL AUTO_INCREMENT,
  `nombre_permiso` varchar(100) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('comestible','mobiliario') NOT NULL,
  `unidad_medida` varchar(50) DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `tipo`, `unidad_medida`, `precio_unitario`) VALUES
(1, 'Lechuga', 'comestible', 'kg', 5.00),
(2, 'Pollo', 'comestible', 'kg', 25.00),
(3, 'Carne Res', 'comestible', 'kg', 35.00),
(4, 'Arroz', 'comestible', 'kg', 8.00),
(5, 'Pasta', 'comestible', 'kg', 12.00),
(6, 'Tomate', 'comestible', 'kg', 6.00),
(7, 'Cebolla', 'comestible', 'kg', 4.00),
(8, 'Naranja', 'comestible', 'kg', 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_proveedor` varchar(100) NOT NULL,
  `persona_contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre_proveedor`, `persona_contacto`, `telefono`, `correo`, `direccion`, `categoria`, `estado`) VALUES
(1, 'Distribuidora de Alimentos SA', 'Carlos Martínez', '2233-4455', 'ventas@dalimentos.com', 'Zona 1, Ciudad', 'Alimentos', 'Activo'),
(2, 'Carnicería El Buen Corte', 'Ana López', '5544-6677', 'pedidos@elbuencorte.com', 'Zona 5, Ciudad', 'Carnes', 'Activo'),
(3, 'Verdulería Fresca', 'María González', '7788-9900', 'info@verdufresca.com', 'Zona 8, Ciudad', 'Verduras', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

DROP TABLE IF EXISTS `recetas`;
CREATE TABLE IF NOT EXISTS `recetas` (
  `id_receta` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_receta`),
  UNIQUE KEY `id_menu` (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`id_receta`, `id_menu`, `descripcion`) VALUES
(1, 1, 'Preparar huevos al gusto, freír plátanos, preparar frijoles, servir con queso, crema y longaniza'),
(2, 3, 'Preparar omelette con chorizo ahumado, tomate, cebolla y chile pimiento. Acompañar con frijol parado y tortilla con queso'),
(3, 6, 'Huevos estrellados bañados en salsa ranchera, acompañados de frijol, plátanos fritos, queso, crema y tortilla con queso derretido'),
(4, 23, 'Preparar recado verde con miltomates y cilantro, cocinar con pollo o res. Servir con arroz blanco'),
(5, 27, 'Preparar recado de tomate y pepitoria, cocinar con pollo o res. Servir con arroz típico'),
(6, 45, 'Marinar carne con adobo especial, asar a la parrilla. Servir con puré de papa, ensalada y aguacate'),
(7, 60, 'Preparar pinchos de lomito con piña, chile pimiento y cebolla. Asar a la parrilla y servir con arroz y ensalada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `retiros`
--

DROP TABLE IF EXISTS `retiros`;
CREATE TABLE IF NOT EXISTS `retiros` (
  `id_retiro` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `tipo` enum('comestible','mobiliario') NOT NULL,
  `cantidad` int NOT NULL,
  `razon` varchar(255) NOT NULL,
  `fecha_retiro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_retiro`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(100) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso completo al sistema'),
(2, 'Mesero', 'Atención a mesas y toma de pedidos'),
(3, 'Cocinero', 'Preparación de alimentos'),
(4, 'Cajero', 'Manejo de caja y facturación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salones`
--

DROP TABLE IF EXISTS `salones`;
CREATE TABLE IF NOT EXISTS `salones` (
  `id_salon` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `id_sucursal` int NOT NULL,
  PRIMARY KEY (`id_salon`),
  KEY `id_sucursal` (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `salones`
--

INSERT INTO `salones` (`id_salon`, `nombre`, `id_sucursal`) VALUES
(1, 'Salón Principal', 1),
(2, 'Salón Magno', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursales`
--

DROP TABLE IF EXISTS `sucursales`;
CREATE TABLE IF NOT EXISTS `sucursales` (
  `id_sucursal` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `sucursales`
--

INSERT INTO `sucursales` (`id_sucursal`, `nombre`, `direccion`) VALUES
(1, 'Sucursal Temporal', 'Dirección temporal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre_usuario` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombre_completo` varchar(150) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `identificador` tinyint(1) DEFAULT '0',
  `id_rol` int DEFAULT NULL,
  `token_recuperacion` varchar(50) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `id_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `contrasena`, `nombre_completo`, `estado`, `identificador`, `id_rol`, `token_recuperacion`, `token_expira`, `correo`, `fecha_nacimiento`, `telefono`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'Activo', 0, 1, NULL, NULL, 'admin@eladobe.com', NULL, '12345678'),
(2, 'mesero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez Mesero', 'Activo', 0, 2, NULL, NULL, 'mesero1@eladobe.com', NULL, '87654321'),
(3, 'cocinero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García Cocina', 'Activo', 0, 3, NULL, NULL, 'cocina@eladobe.com', NULL, '55555555'),
(4, 'Fermi', '$2y$10$gno031X4MqHabqg/PpCvluhV//73BG/5Ha3spL0iqk/33WCYtNh1.', 'Fer Mir', 'Activo', 0, 2, NULL, NULL, 'fermi@gmail.com', '2004-08-09', '55555555');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

DROP TABLE IF EXISTS `ventas`;
CREATE TABLE IF NOT EXISTS `ventas` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_mesa` int DEFAULT NULL,
  `id_usuario` int NOT NULL,
  `total` decimal(10,2) DEFAULT '0.00',
  `metodo_pago` enum('efectivo','tarjeta','transferencia','mixto') DEFAULT 'efectivo',
  `estado` enum('pendiente','pagada','anulada') DEFAULT 'pendiente',
  PRIMARY KEY (`id_venta`),
  KEY `id_mesa` (`id_mesa`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_inventario_comestibles`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vista_inventario_comestibles`;
CREATE TABLE IF NOT EXISTS `vista_inventario_comestibles` (
`id_producto` int
,`nombre` varchar(100)
,`categoria` varchar(50)
,`unidad_medida` varchar(50)
,`stock` int
,`stock_minimo` int
,`precio_unitario` decimal(10,2)
,`fecha_agregado` date
,`ultima_actualizacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_inventario_mobiliario`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vista_inventario_mobiliario`;
CREATE TABLE IF NOT EXISTS `vista_inventario_mobiliario` (
`id_producto` int
,`nombre` varchar(100)
,`categoria` varchar(50)
,`unidad_medida` varchar(50)
,`stock` int
,`stock_minimo` int
,`precio_unitario` decimal(10,2)
,`fecha_agregado` date
,`ultima_actualizacion` timestamp
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_menu_completo`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vista_menu_completo`;
CREATE TABLE IF NOT EXISTS `vista_menu_completo` (
`id_menu` int
,`nombre` varchar(100)
,`descripcion` text
,`precio` decimal(10,2)
,`categoria` varchar(50)
,`activo` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_pedidos_activos`
-- (Véase abajo para la vista actual)
--
DROP VIEW IF EXISTS `vista_pedidos_activos`;
CREATE TABLE IF NOT EXISTS `vista_pedidos_activos` (
`id_pedido` int
,`mesa` int
,`mesero` varchar(150)
,`estado` enum('pendiente','enviado','preparando','listo','entregado','finalizado','facturado','cancelado')
,`total` decimal(10,2)
,`fecha` datetime
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_inventario_comestibles`
--
DROP TABLE IF EXISTS `vista_inventario_comestibles`;

DROP VIEW IF EXISTS `vista_inventario_comestibles`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_inventario_comestibles`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`nombre` AS `nombre`, `c`.`categoria` AS `categoria`, `p`.`unidad_medida` AS `unidad_medida`, `c`.`stock` AS `stock`, `c`.`stock_minimo` AS `stock_minimo`, `p`.`precio_unitario` AS `precio_unitario`, `c`.`fecha_agregado` AS `fecha_agregado`, `c`.`ultima_actualizacion` AS `ultima_actualizacion` FROM (`productos` `p` join `comestibles` `c` on((`p`.`id_producto` = `c`.`id_producto`))) ORDER BY `p`.`nombre` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_inventario_mobiliario`
--
DROP TABLE IF EXISTS `vista_inventario_mobiliario`;

DROP VIEW IF EXISTS `vista_inventario_mobiliario`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_inventario_mobiliario`  AS SELECT `p`.`id_producto` AS `id_producto`, `p`.`nombre` AS `nombre`, `m`.`categoria` AS `categoria`, `p`.`unidad_medida` AS `unidad_medida`, `m`.`stock` AS `stock`, `m`.`stock_minimo` AS `stock_minimo`, `p`.`precio_unitario` AS `precio_unitario`, `m`.`fecha_agregado` AS `fecha_agregado`, `m`.`ultima_actualizacion` AS `ultima_actualizacion` FROM (`productos` `p` join `mobiliario_equipo` `m` on((`p`.`id_producto` = `m`.`id_producto`))) ORDER BY `p`.`nombre` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_menu_completo`
--
DROP TABLE IF EXISTS `vista_menu_completo`;

DROP VIEW IF EXISTS `vista_menu_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_menu_completo`  AS SELECT `m`.`id_menu` AS `id_menu`, `m`.`nombre` AS `nombre`, `m`.`descripcion` AS `descripcion`, `m`.`precio` AS `precio`, `c`.`nombre` AS `categoria`, `m`.`activo` AS `activo` FROM (`menu` `m` join `categorias` `c` on((`m`.`id_categoria` = `c`.`id_categoria`))) ORDER BY `c`.`nombre` ASC, `m`.`nombre` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_pedidos_activos`
--
DROP TABLE IF EXISTS `vista_pedidos_activos`;

DROP VIEW IF EXISTS `vista_pedidos_activos`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vista_pedidos_activos`  AS SELECT `p`.`id_pedido` AS `id_pedido`, `m`.`numero` AS `mesa`, `u`.`nombre_completo` AS `mesero`, `p`.`estado` AS `estado`, `p`.`total` AS `total`, `p`.`fecha` AS `fecha` FROM ((`pedidos` `p` join `mesas` `m` on((`p`.`id_mesa` = `m`.`id_mesa`))) join `usuarios` `u` on((`p`.`id_usuario` = `u`.`id_usuario`))) WHERE (`p`.`estado` not in ('finalizado','cancelado','facturado')) ORDER BY `p`.`fecha` DESC ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_permiso`
--
ALTER TABLE `asignacion_permiso`
  ADD CONSTRAINT `fk_asignacion_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  ADD CONSTRAINT `fk_asignacion_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `bitacora_movimientos`
--
ALTER TABLE `bitacora_movimientos`
  ADD CONSTRAINT `fk_bitacora_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `comestibles`
--
ALTER TABLE `comestibles`
  ADD CONSTRAINT `fk_comestible_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compra_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cuentas_separadas`
--
ALTER TABLE `cuentas_separadas`
  ADD CONSTRAINT `cuentas_separadas_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`);

--
-- Filtros para la tabla `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD CONSTRAINT `fk_detalle_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_cuenta`
--
ALTER TABLE `detalle_cuenta`
  ADD CONSTRAINT `detalle_cuenta_ibfk_1` FOREIGN KEY (`id_cuenta`) REFERENCES `cuentas_separadas` (`id_cuenta`),
  ADD CONSTRAINT `detalle_cuenta_ibfk_2` FOREIGN KEY (`id_detalle_pedido`) REFERENCES `detalle_pedido` (`id_detalle_pedido`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Filtros para la tabla `detalle_receta`
--
ALTER TABLE `detalle_receta`
  ADD CONSTRAINT `detalle_receta_ibfk_1` FOREIGN KEY (`id_receta`) REFERENCES `recetas` (`id_receta`),
  ADD CONSTRAINT `detalle_receta_ibfk_2` FOREIGN KEY (`id_comestible`) REFERENCES `comestibles` (`id_comestible`);

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`),
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`),
  ADD CONSTRAINT `facturas_ibfk_2` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`);

--
-- Filtros para la tabla `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD CONSTRAINT `mesas_ibfk_1` FOREIGN KEY (`id_mesero_asignado`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `mobiliario_equipo`
--
ALTER TABLE `mobiliario_equipo`
  ADD CONSTRAINT `fk_mobiliario_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Filtros para la tabla `retiros`
--
ALTER TABLE `retiros`
  ADD CONSTRAINT `fk_retiro_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `salones`
--
ALTER TABLE `salones`
  ADD CONSTRAINT `salones_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id_sucursal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
