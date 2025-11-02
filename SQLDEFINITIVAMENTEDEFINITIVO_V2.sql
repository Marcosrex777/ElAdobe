-- ============================================
-- 🧱 BASE DE DATOS LIMPIA: eladobe
-- ============================================

DROP DATABASE IF EXISTS eladobe;
CREATE DATABASE eladobe CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE eladobe;

-- ============================================
-- TABLAS PRINCIPALES
-- ============================================

CREATE TABLE roles (
  id_rol INT NOT NULL AUTO_INCREMENT,
  nombre_rol VARCHAR(100) NOT NULL,
  descripcion TEXT,
  PRIMARY KEY (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE permisos (
  id_permiso INT NOT NULL AUTO_INCREMENT,
  nombre_permiso VARCHAR(100) NOT NULL,
  descripcion TEXT,
  PRIMARY KEY (id_permiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE asignacion_permiso (
  id_asignacion INT NOT NULL AUTO_INCREMENT,
  id_rol INT DEFAULT NULL,
  id_permiso INT DEFAULT NULL,
  PRIMARY KEY (id_asignacion),
  KEY (id_rol),
  KEY (id_permiso),
  CONSTRAINT fk_asignacion_rol FOREIGN KEY (id_rol) REFERENCES roles (id_rol),
  CONSTRAINT fk_asignacion_permiso FOREIGN KEY (id_permiso) REFERENCES permisos (id_permiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE usuarios (
  id_usuario INT NOT NULL AUTO_INCREMENT,
  nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  nombre_completo VARCHAR(150),
  estado ENUM('Activo','Inactivo') DEFAULT 'Activo',
  identificador TINYINT(1) DEFAULT 0,
  id_rol INT DEFAULT NULL,
  token_recuperacion VARCHAR(50),
  token_expira DATETIME,
  correo VARCHAR(50),
  fecha_nacimiento DATE,
  telefono VARCHAR(50),
  PRIMARY KEY (id_usuario),
  KEY (id_rol),
  CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES roles (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE bitacora_movimientos (
  id_movimiento INT NOT NULL AUTO_INCREMENT,
  id_usuario INT DEFAULT NULL,
  modulo VARCHAR(100),
  accion VARCHAR(255),
  ip_usuario VARCHAR(45),
  fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_movimiento),
  KEY (id_usuario),
  CONSTRAINT fk_bitacora_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE proveedores (
  id INT NOT NULL AUTO_INCREMENT,
  nombre_proveedor VARCHAR(100) NOT NULL,
  persona_contacto VARCHAR(100),
  telefono VARCHAR(20),
  correo VARCHAR(100),
  direccion VARCHAR(150),
  categoria VARCHAR(50),
  estado ENUM('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE productos (
  id_producto INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  tipo ENUM('comestible','mobiliario') NOT NULL,
  unidad_medida VARCHAR(50),
  precio_unitario DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE comestibles (
  id_comestible INT NOT NULL AUTO_INCREMENT,
  id_producto INT NOT NULL,
  categoria VARCHAR(50),
  cantidad INT DEFAULT 0,
  stock INT DEFAULT 0,
  stock_minimo INT DEFAULT 0,
  fecha_agregado DATE DEFAULT (CURDATE()),
  ultima_actualizacion TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_comestible),
  KEY (id_producto),
  CONSTRAINT fk_comestible_producto FOREIGN KEY (id_producto) REFERENCES productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE mobiliario_equipo (
  id_mobiliario INT NOT NULL AUTO_INCREMENT,
  id_producto INT NOT NULL,
  categoria VARCHAR(50),
  cantidad INT DEFAULT 0,
  stock INT DEFAULT 0,
  stock_minimo INT DEFAULT 0,
  fecha_agregado DATE DEFAULT (CURDATE()),
  ultima_actualizacion TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_mobiliario),
  KEY (id_producto),
  CONSTRAINT fk_mobiliario_producto FOREIGN KEY (id_producto) REFERENCES productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE compras (
  id_compra INT NOT NULL AUTO_INCREMENT,
  id_proveedor INT NOT NULL,
  fecha DATE NOT NULL,
  metodo_pago ENUM('Efectivo','Transferencia','Tarjeta','Cheque') DEFAULT 'Efectivo',
  numero_comprobante VARCHAR(50),
  total DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_compra),
  KEY (id_proveedor),
  CONSTRAINT fk_compra_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE detalle_compras (
  id_detalle INT NOT NULL AUTO_INCREMENT,
  id_compra INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id_detalle),
  KEY (id_compra),
  KEY (id_producto),
  CONSTRAINT fk_detalle_compra FOREIGN KEY (id_compra) REFERENCES compras (id_compra) ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE retiros (
  id_retiro INT NOT NULL AUTO_INCREMENT,
  id_producto INT NOT NULL,
  tipo ENUM('comestible','mobiliario') NOT NULL,
  cantidad INT NOT NULL,
  razon VARCHAR(255) NOT NULL,
  fecha_retiro DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_retiro),
  KEY (id_producto),
  CONSTRAINT fk_retiro_producto FOREIGN KEY (id_producto) REFERENCES productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 🪑 TABLAS DE RESTAURANTE (NUEVAS)
-- ============================================

-- Mesas del restaurante
CREATE TABLE mesas (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT DEFAULT 4,
    estado ENUM('libre', 'ocupada', 'reservada') DEFAULT 'libre',
    id_mesero_asignado INT NULL,
    FOREIGN KEY (id_mesero_asignado) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Categorías del menú
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Menú del restaurante
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    id_categoria INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Recetas de los platillos
CREATE TABLE recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT UNIQUE NOT NULL,
    descripcion TEXT,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Detalle de ingredientes por receta
CREATE TABLE detalle_receta (
    id_detalle_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_receta INT NOT NULL,
    id_comestible INT NOT NULL,
    cantidad_usada DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(20),
    FOREIGN KEY (id_receta) REFERENCES recetas(id_receta),
    FOREIGN KEY (id_comestible) REFERENCES comestibles(id_comestible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Ventas del restaurante
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_mesa INT,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('efectivo','tarjeta','transferencia','mixto') DEFAULT 'efectivo',
    estado ENUM('pendiente','pagada','anulada') DEFAULT 'pendiente',
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Detalle de ventas
CREATE TABLE detalle_venta (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_menu INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Pedidos de los clientes
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','enviado','preparando','listo','entregado','finalizado','facturado','cancelado') DEFAULT 'pendiente',
    total DECIMAL(10,2) DEFAULT 0,
    fecha_envio DATETIME NULL,
    fecha_preparacion DATETIME NULL,
    fecha_listo DATETIME NULL,
    fecha_entregado DATETIME NULL,
    fecha_finalizado DATETIME NULL,
    fecha_facturado DATETIME NULL,
    fecha_cancelado DATETIME NULL,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Detalle de pedidos
CREATE TABLE detalle_pedido (
    id_detalle_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_menu INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Facturas
CREATE TABLE facturas (
    id_factura INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_pedido INT NOT NULL,
    numero_factura VARCHAR(20) UNIQUE,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,2),
    iva DECIMAL(10,2) DEFAULT 0,
    propina DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2),
    cliente_nombre VARCHAR(255) NULL,
    cliente_nit VARCHAR(20) NULL,
    direccion TEXT NULL,
    metodo_pago ENUM('efectivo','tarjeta','transferencia','mixto'),
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Cuentas separadas para dividir la cuenta
CREATE TABLE cuentas_separadas (
    id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    numero_cuenta INT NOT NULL,
    cliente_nombre VARCHAR(255),
    cliente_nit VARCHAR(20),
    subtotal DECIMAL(10,2) DEFAULT 0,
    iva DECIMAL(10,2) DEFAULT 0,
    propina DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('efectivo','tarjeta','transferencia','mixto'),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Detalle de cuentas separadas
CREATE TABLE detalle_cuenta (
    id_detalle_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta INT NOT NULL,
    id_detalle_pedido INT NOT NULL,
    cantidad_asignada INT NOT NULL,
    FOREIGN KEY (id_cuenta) REFERENCES cuentas_separadas(id_cuenta),
    FOREIGN KEY (id_detalle_pedido) REFERENCES detalle_pedido(id_detalle_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ============================================
-- 🔄 TRIGGERS
-- ============================================

DELIMITER //
CREATE TRIGGER trg_actualizar_stock
AFTER INSERT ON detalle_compras
FOR EACH ROW
BEGIN
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
END;
//
DELIMITER ;

-- ============================================
-- 👁️ VISTAS
-- ============================================

CREATE VIEW vista_inventario_comestibles AS
SELECT 
  p.id_producto,
  p.nombre,
  c.categoria,
  p.unidad_medida,
  c.stock,
  c.stock_minimo,
  p.precio_unitario,
  c.fecha_agregado,
  c.ultima_actualizacion
FROM productos p
JOIN comestibles c ON p.id_producto = c.id_producto
ORDER BY p.nombre;

CREATE VIEW vista_inventario_mobiliario AS
SELECT 
  p.id_producto,
  p.nombre,
  m.categoria,
  p.unidad_medida,
  m.stock,
  m.stock_minimo,
  p.precio_unitario,
  m.fecha_agregado,
  m.ultima_actualizacion
FROM productos p
JOIN mobiliario_equipo m ON p.id_producto = m.id_producto
ORDER BY p.nombre;

-- Vista para menú completo
CREATE VIEW vista_menu_completo AS
SELECT 
    m.id_menu,
    m.nombre,
    m.descripcion,
    m.precio,
    c.nombre as categoria,
    m.activo
FROM menu m
JOIN categorias c ON m.id_categoria = c.id_categoria
ORDER BY c.nombre, m.nombre;

-- Vista para pedidos en proceso
CREATE VIEW vista_pedidos_activos AS
SELECT 
    p.id_pedido,
    m.numero as mesa,
    u.nombre_completo as mesero,
    p.estado,
    p.total,
    p.fecha
FROM pedidos p
JOIN mesas m ON p.id_mesa = m.id_mesa
JOIN usuarios u ON p.id_usuario = u.id_usuario
WHERE p.estado NOT IN ('finalizado', 'cancelado', 'facturado')
ORDER BY p.fecha DESC;

-- ============================================
-- 📊 DATOS DE PRUEBA - ORDEN CORRECTO
-- ============================================

-- 1. Insertar roles básicos
INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Acceso completo al sistema'),
('Mesero', 'Atención a mesas y toma de pedidos'),
('Cocinero', 'Preparación de alimentos'),
('Cajero', 'Manejo de caja y facturación');

-- 2. Insertar usuarios de prueba
INSERT INTO usuarios (nombre_usuario, contrasena, nombre_completo, estado, id_rol, correo, telefono) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'Activo', 1, 'admin@eladobe.com', '12345678'),
('mesero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez Mesero', 'Activo', 2, 'mesero1@eladobe.com', '87654321'),
('cocinero1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García Cocina', 'Activo', 3, 'cocina@eladobe.com', '55555555');

-- 3. Insertar categorías del menú
INSERT INTO categorias (nombre) VALUES
('Desayunos'),
('Entradas'),
('Almuerzos y Cenas'),
('Postres'),
('Bebidas');

-- 4. Insertar mesas
INSERT INTO mesas (numero, capacidad, estado, id_mesero_asignado) VALUES
(1, 4, 'libre', 2),
(2, 6, 'libre', 2),
(3, 2, 'libre', 2),
(4, 4, 'libre', 2),
(5, 8, 'libre', 2);

-- 5. Insertar productos base (comestibles)
INSERT INTO productos (nombre, tipo, unidad_medida, precio_unitario) VALUES
('Lechuga', 'comestible', 'kg', 5.00),
('Pollo', 'comestible', 'kg', 25.00),
('Carne Res', 'comestible', 'kg', 35.00),
('Arroz', 'comestible', 'kg', 8.00),
('Pasta', 'comestible', 'kg', 12.00),
('Tomate', 'comestible', 'kg', 6.00),
('Cebolla', 'comestible', 'kg', 4.00),
('Naranja', 'comestible', 'kg', 10.00);

-- 6. Insertar en comestibles
INSERT INTO comestibles (id_producto, categoria, stock, stock_minimo) VALUES
(1, 'Verduras', 10, 2),
(2, 'Carnes', 15, 5),
(3, 'Carnes', 20, 8),
(4, 'Granos', 30, 10),
(5, 'Granos', 25, 8),
(6, 'Verduras', 15, 5),
(7, 'Verduras', 12, 4),
(8, 'Frutas', 18, 6);

-- 7. Insertar proveedores
INSERT INTO proveedores (nombre_proveedor, persona_contacto, telefono, correo, direccion, categoria, estado) VALUES
('Distribuidora de Alimentos SA', 'Carlos Martínez', '2233-4455', 'ventas@dalimentos.com', 'Zona 1, Ciudad', 'Alimentos', 'Activo'),
('Carnicería El Buen Corte', 'Ana López', '5544-6677', 'pedidos@elbuencorte.com', 'Zona 5, Ciudad', 'Carnes', 'Activo'),
('Verdulería Fresca', 'María González', '7788-9900', 'info@verdufresca.com', 'Zona 8, Ciudad', 'Verduras', 'Activo');

-- ==============================================
-- 8. INSERTAR MENÚ COMPLETO - CORREGIDO
-- ==============================================

-- ==============================================
-- 8.1. DESAYUNOS
-- ==============================================
INSERT INTO menu (nombre, descripcion, precio, id_categoria) VALUES
('Típico', 'Huevos al gusto, acompañados de plátanos fritos, frijoles, queso, crema y longaniza', 70.00, 1),
('Huevos Ixín', 'Huevos estrellados servidos sobre tortilla frita y salsa verde, plátanos fritos, frijoles colados, queso, crema y longaniza', 75.00, 1),
('Desayuno de Mi Pueblo', 'Omelette relleno de chorizo ahumado con sofrito de tomate, cebolla y chile pimiento acompañado de frijol parado y tortilla con queso', 80.00, 1),
('Desayuno Paxot', 'Huevos estrellados cocinados al comal bañados en salsa Subanik, chorizo, tortilla frita y queso criollo', 85.00, 1),
('Desayuno Granjero', 'Huevos estrellados montados sobre carne de res, frijoles y tortilla con queso derretido', 95.00, 1),
('Huevos Rancheros', 'Huevos estrellados bañados en salsa ranchera acompañada de frijol, plátanos fritos, queso, crema y tortilla con queso derretido y longaniza', 80.00, 1),
('Omelette de Claras', 'Relleno de champiñón, espinaca, queso mozzarella y chile pimiento, acompañado de plátanos cocidos y bowl de fruta', 75.00, 1),
('Desayuno Oriente', 'Omelette relleno de loroco, chile pimiento, cebolla y jamón', 80.00, 1),
('El Adobe', 'Huevos al gusto, queso, crema, plátanos, salsa y 4oz de carne adobada', 90.00, 1),
('Omelette Tradicional', 'Relleno de jamón, champiñones y queso', 80.00, 1),
('Tamales', 'Receta tradicional sellada en hoja de mazán', 45.00, 1),
('Caldo de Huevo', '', 60.00, 1),
('Moshito', 'Atol acompañado con plátanos cocidos', 40.00, 1),
('Panqueques', 'Mezcla exclusiva de El Adobe, acompañados con miel de abeja o maple', 70.00, 1),
('Tostadas a la Francesa', 'Con pan brioche', 75.00, 1),
('Tazón de Frutas', 'Yogurt acompañado de fresas, arándanos, kiwi, pepitoria, nueces nogal, pasas, chan y coco rallado', 75.00, 1),
('Fruta de la Estación', 'Festín de frutas frescas, acompañadas con yogurt, granola y miel', 75.00, 1);

-- ==============================================
-- 8.2. ENTRADAS
-- ==============================================
INSERT INTO menu (nombre, descripcion, precio, id_categoria) VALUES
('Tostadas Variadas (4)', 'De chojín, buche y salpicón', 60.00, 2),
('Tostadas Chapinas (4)', 'Guacamol, frijol, salsa', 50.00, 2),
('Tortillas con Queso Derretido (2)', '', 35.00, 2),
('Tortillas con Longaniza o Chorizo Ahumado (2)', 'Cebollín, guacamol y chirmol', 45.00, 2),
('Tortillas con Morcilla (4)', '', 55.00, 2),
('Garnachas', '', 40.00, 2),
('Chuchitos (2)', '', 30.00, 2),
('Tamalitos de Chipilín (3)', '', 35.00, 2),
('Enchilada', 'Tostada con curtido de remolacha, zanahoria y ejote', 45.00, 2),
('Chiles Rellenos (2)', '', 65.00, 2),
('Mixtas de Salchicha o Chorizo (4)', 'Tortillas con guacamol, repollo, salchicha, ketchup y mayonesa', 50.00, 2),
('Elotes Locos (4)', '', 40.00, 2),
('Crema de Frijol', '', 35.00, 2),
('Mixtas de Chorizo Ahumado', '', 55.00, 2);

-- ==============================================
-- 8.3. ALMUERZOS Y CENAS
-- ==============================================
INSERT INTO menu (nombre, descripcion, precio, id_categoria) VALUES
-- Platos Tradicionales
('Jocón de Pollo o Res', 'Plato de occidente, recado verde a base de miltomates y cilantro servido con arroz blanco', 100.00, 3),
('Jocón de Pechuga de Pollo', 'Plato de occidente, recado verde a base de miltomates y cilantro servido con arroz blanco', 110.00, 3),
('Jocón de Gallina Criolla', 'Plato de occidente, recado verde a base de miltomates y cilantro servido con arroz blanco', 130.00, 3),
('Kaqik', 'Sopa ceremonial de Cobán con variedad de especias de la región', 200.00, 3),
('Pepián de Pollo o Res', 'Plato típico de occidente. Recado de tomate y pepitoria servido con arroz típico', 100.00, 3),
('Pepián de Pechuga de Pollo', 'Plato típico de occidente. Recado de tomate y pepitoria servido con arroz típico', 110.00, 3),
('Pepián de Gallina Criolla', 'Plato típico de occidente. Recado de tomate y pepitoria servido con arroz típico', 130.00, 3),
('Pinol de Gallina Criolla', 'Plato prehispánico originario de San Juan Sacatepéquez, a base de maíz dorado', 130.00, 3),

-- Caldos y Sopas
('Caldo de Res', 'Cocido de res, zanahoria, papa, güisquil, elote, repollo y güicoy. Acompañado de tortillas, arroz y aguacate', 125.00, 3),
('Caldo de Gallina Criolla', 'Gallina criolla cocida o asada, acompañado de tortillas, verduras, arroz y aguacate', 150.00, 3),
('Tapado', 'Platillo típico de Livingston, Izabal', 200.00, 3),
('Caldo de Mariscos', '', 190.00, 3),

-- Especialidades de la Casa
('Carne Adobada', 'Servida con puré de papa, ensalada y porción de aguacate', 120.00, 3),
('Camarones Empanizados o al Ajillo', 'Acompañados de verduras salteadas y arroz', 180.00, 3),
('Camarones Utzaj', '', 170.00, 3),
('Ceviche de Camarón', 'Tomate picado, cebolla, hierbabuena, limón y camarón', 90.00, 3),
('Mojarra', 'Servida con ensalada y papas asadas', 140.00, 3),
('Pollo o Gallina en Crema', 'Bañado en salsa de crema y loroco, acompañado de arroz y elote dulce', 110.00, 3),
('Pechuga en Pimientos', 'Pechuga de pollo frita bañada en salsa de pimientos, acompañada con cacerola de güicoy con queso y ensalada escabeche', 130.00, 3),
('Pechuga a la Parrilla', 'Filete a la parrilla, acompañado con mix de lechuga y verduras cocidas', 120.00, 3),

-- Platos Especiales
('Ojer Kutun', 'Comida Ancestral representada en cinco platillos de la gastronomía maya', 120.00, 3),
('Subanik', 'Platillo ceremonial de San Martín Jilotepaque, preparado con combinación de carnes y chiles, cocido a fuego lento dentro de hojas de moxán', 110.00, 3),
('Revolcado', 'Recado tradicional de Sacatepéquez, hecho a base de cabeza y vísceras de cerdo', 100.00, 3),
('Frijol Colorado', 'Con castilla de cerdo y chorizo ahumado', 110.00, 3),
('Frijol Blanco con Espinazo', '', 110.00, 3),
('Hilachas', '', 110.00, 3),
('Panza de Res', '', 100.00, 3),
('Lengua Guisada', '', 100.00, 3),

-- Parrilla y Carnes
('Costilla en Barbacoa', 'Acompañada de puré de papa y elote dulce', 110.00, 3),
('Fajitas de Res o Pollo', 'Con chile pimiento y cebolla acompañadas de guacamol y arroz', 110.00, 3),
('Pinchos', 'Pinchos de lomito, piña, chile pimiento y cebolla a la parrilla, servidos en cama de arroz y ensalada de la casa', 130.00, 3),
('Pinchos Mar y Tierra', '', 170.00, 3),
('Puñazo o Lomito', 'Carne de 8oz a la parrilla acompañada de cebollines, papa asada, guacamol y frijol', 150.00, 3),
('Entraña 12oz', 'Servida con guacamol, cebollines asados, frijol y papa asada', 200.00, 3),
('Entraña 8oz', 'Servida con guacamol, cebollines asados, frijol y papa asada', 170.00, 3),
('Chorizo o Longaniza 1lb', 'Frijoles volteados, guacamol y tortillas', 120.00, 3),
('Chorizo o Longaniza 1/2lb', 'Frijoles volteados, guacamol y tortillas', 80.00, 3),
('Chorizo Ahumado 1lb', 'Frijoles volteados, guacamol y tortillas', 130.00, 3),
('Chorizo Ahumado 1/2lb', 'Frijoles volteados, guacamol y tortillas', 90.00, 3),
('Parrillada Especial', '12 lb de lomito, 1/2 lb de puñazo, 1/2 lb de chorizo, porción de frijoles volteados, guacamol, cebollines y papa asada', 295.00, 3),
('Parrillada Familiar', '1lb de puñazo, 1lb de lomito, 1lb de chorizo, 3 piezas de pollo asado, cebollines, frijoles volteados, guacamol y papas asadas', 500.00, 3),

-- Opciones Saludables
('Plato Vegetariano', 'Zuchinni, berenjena, chile pimiento, cebolla, tomate y champiñones', 80.00, 3),
('Ensalada César', 'Lechuga, tomate, aguacate y pechuga de pollo a la parrilla', 80.00, 3),
('Ensalada de Quinoa', '', 80.00, 3),

-- Menú Infantil
('Hamburguesa 1/4 lb', 'Carne a la parrilla, servida con lechuga y tomate, acompañada de papas fritas', 55.00, 3),
('Hamburguesita', 'Carne a la parrilla, servida con lechuga y tomate, acompañada de papas fritas', 45.00, 3),
('Deditos de Pollo', 'Fritos, servidos con salsa dulce y papas fritas', 50.00, 3),
('Queso-Hamburguesa 1/4 lb', 'Carne con queso cheddar derretido, servida con lechuga y tomate, acompañada de papas fritas', 60.00, 3),
('Queso-Hamburguesita', 'Carne con queso cheddar derretido, servida con lechuga y tomate, acompañada de papas fritas', 50.00, 3),
('Shuauito', 'Con salchicha, guacamol y repollo acompañado con papas fritas', 50.00, 3),
('Pollo Frito', 'Acompañado con papas fritas', 50.00, 3);

-- ==============================================
-- 8.4. POSTRES
-- ==============================================
INSERT INTO menu (nombre, descripcion, precio, id_categoria) VALUES
('Chancleta Individual', 'Postre de güisquil', 35.00, 4),
('Chancleta', 'Postre de güisquil', 40.00, 4),
('Male de Plátano', '', 35.00, 4),
('Tartaleta de Fruta', '', 35.00, 4),
('Flan Antigueño', '', 40.00, 4),
('Rellenitos (2)', '', 30.00, 4),
('Tres Leches', '', 40.00, 4),
('Buñuelos (3)', '', 30.00, 4),
('Torreja (1)', '', 30.00, 4),
('Tarta de Elote', '', 30.00, 4);

-- ==============================================
-- 8.5. BEBIDAS
-- ==============================================
INSERT INTO menu (nombre, descripcion, precio, id_categoria) VALUES
-- Jugos Naturales
('Jugo de Naranja Natural 12oz', 'Jugo natural recién exprimido', 25.00, 5),
('Jugo de Naranja Natural 16oz', 'Jugo natural recién exprimido', 30.00, 5),
('Fruit Punch', 'Combinación de jugos naturales y granadina', 25.00, 5),

-- Licuados
('Licuado de Frutas Regular', 'Papaya, piña, melón, sandía, banana, mora, fresa o mixta', 25.00, 5),
('Licuado de Frutas con Leche', 'Papaya, piña, melón, sandía, banana, mora, fresa o mixta', 30.00, 5),
('Licuado de Frutas con Yogurt', 'Papaya, piña, melón, sandía, banana, mora, fresa o mixta', 35.00, 5),

-- Licuados Nutritivos
('Licuado Nutritivo 1', 'Apio, zanahoria, elote dulce y jugo de naranja', 25.00, 5),
('Licuado Nutritivo 2', 'Pepino, jugo de naranja, apio, espinaca y piña', 25.00, 5),
('Licuado Nutritivo 3', 'Aguacate, chan, piña, espinaca y jugo de naranja', 25.00, 5),
('Licuado Nutritivo 4', 'Kiwi, jugo de naranja, apio y piña', 25.00, 5),

-- Bebidas Tradicionales
('Limonada o Naranjada Regular', 'Natural', 25.00, 5),
('Limonada o Naranjada Piche', 'Natural', 90.00, 5),
('Repitaza Simple Regular', '', 25.00, 5),
('Repitaza Simple Piche', '', 90.00, 5),
('Botella de Agua', '', 15.00, 5),
('Cimarrona', 'Agua mineral preparada con sal y limón', 25.00, 5),
('Santa Delfina', '', 28.00, 5),
('Té Frío', '', 25.00, 5),
('Jugo de Tomate Preparado Natural', '', 25.00, 5),

-- Refrescos Naturales
('Refresco Natural Regular', 'Horchata, jamaica, tamarindo, carambola, pepita o tiste', 25.00, 5),
('Refresco Natural Piche', 'Horchata, jamaica, tamarindo, carambola, pepita o tiste', 90.00, 5),

-- Gaseosas
('Gaseosa', 'Pepsi, 7Up, Pepsi Zero Azúcar, 7Up Light', 18.00, 5),

-- Smoothies
('Smoothie', 'Tamarindo, berries, tropical, mango, piña colada y carambola', 25.00, 5),

-- Cerveza y Micheladas
('Michelada', 'Especial de El Adobe con jugo natural de la casa', 40.00, 5),
('Michelada con Cerveza Premium', '', 44.00, 5),

-- Café y Té
('Arroz en Leche', '', 20.00, 5),
('Espresso Regular', '', 12.00, 5),
('Espresso Doble', '', 16.00, 5),
('Cortado Regular', '', 14.00, 5),
('Cortado Doble', '', 18.00, 5),
('Macchiato', '', 15.00, 5),
('Americano', '', 17.00, 5),
('Cappuccino', '', 20.00, 5),
('Latte', '', 20.00, 5),
('Latte Saborizado', '', 25.00, 5),
('Mocaccino', '', 25.00, 5),
('Moka Artesanal', '', 25.00, 5),
('Latte Frío', '', 23.00, 5),
('Té', '', 15.00, 5),
('Té Chai', '', 25.00, 5),
('Té Chai con Leche', '', 30.00, 5),
('Chocolate Regular', '', 20.00, 5),
('Chocolate Doble', '', 23.00, 5),
('Té Matcha Regular', '', 26.00, 5),
('Té Matcha Doble', '', 30.00, 5),
('Frappuccino Regular', '', 28.00, 5),
('Frappuccino Doble', '', 32.00, 5);

-- ==============================================
-- 9. INSERTAR RECETAS - CON ID_MENU CORRECTOS
-- ==============================================

-- Insertar recetas para algunos platillos principales
INSERT INTO recetas (id_menu, descripcion) VALUES
(1, 'Preparar huevos al gusto, freír plátanos, preparar frijoles, servir con queso, crema y longaniza'),
(3, 'Preparar omelette con chorizo ahumado, tomate, cebolla y chile pimiento. Acompañar con frijol parado y tortilla con queso'),
(6, 'Huevos estrellados bañados en salsa ranchera, acompañados de frijol, plátanos fritos, queso, crema y tortilla con queso derretido'),
(23, 'Preparar recado verde con miltomates y cilantro, cocinar con pollo o res. Servir con arroz blanco'),
(27, 'Preparar recado de tomate y pepitoria, cocinar con pollo o res. Servir con arroz típico'),
(45, 'Marinar carne con adobo especial, asar a la parrilla. Servir con puré de papa, ensalada y aguacate'),
(60, 'Preparar pinchos de lomito con piña, chile pimiento y cebolla. Asar a la parrilla y servir con arroz y ensalada');

-- ==============================================
-- 10. INSERTAR DETALLE_RECETA - CON ID_RECETA CORRECTOS
-- ==============================================

-- Los id_receta ahora son 1, 2, 3, 4, 5, 6, 7 (corresponden a los inserts anteriores)
INSERT INTO detalle_receta (id_receta, id_comestible, cantidad_usada, unidad) VALUES
(1, 1, 0.20, 'kg'),   -- Receta 1: Típico - Lechuga
(1, 2, 0.15, 'kg'),   -- Receta 1: Típico - Pollo
(2, 2, 0.25, 'kg'),   -- Receta 2: Desayuno de Mi Pueblo - Pollo  
(3, 3, 0.30, 'kg'),   -- Receta 3: Huevos Rancheros - Carne Res
(4, 4, 0.18, 'kg'),   -- Receta 4: Jocón de Pollo - Arroz
(5, 5, 0.22, 'kg'),   -- Receta 5: Pepián de Pollo - Pasta
(6, 8, 0.50, 'kg');   -- Receta 6: Carne Adobada - Naranja