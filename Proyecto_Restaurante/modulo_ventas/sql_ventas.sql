-- ============================================
-- 🧾 BASE DE DATOS: MODULO VENTAS (ACTUALIZADO)
-- ============================================

DROP DATABASE IF EXISTS venta_adobe;
CREATE DATABASE venta_adobe;
USE venta_adobe;

-- ============================================
-- 🔸 ELIMINAR TABLAS SI EXISTEN (ORDEN CORRECTO)
-- ============================================
DROP TABLE IF EXISTS Detalle_Cuenta;
DROP TABLE IF EXISTS Cuentas_Separadas;
DROP TABLE IF EXISTS Facturas;
DROP TABLE IF EXISTS Detalle_Pedido;
DROP TABLE IF EXISTS Pedidos;
DROP TABLE IF EXISTS Detalle_Venta;
DROP TABLE IF EXISTS Ventas;
DROP TABLE IF EXISTS Detalle_Receta;
DROP TABLE IF EXISTS Recetas;
DROP TABLE IF EXISTS Menu;
DROP TABLE IF EXISTS Categorias;
DROP TABLE IF EXISTS Comestibles;
DROP TABLE IF EXISTS Inventario;
DROP TABLE IF EXISTS Mesas;
DROP TABLE IF EXISTS Usuarios;
DROP TABLE IF EXISTS Roles;

-- ============================================
-- 1. Roles
-- ============================================
CREATE TABLE Roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(100) NOT NULL,
    descripcion TEXT
);

INSERT INTO Roles (nombre_rol, descripcion) VALUES
('Administrador', 'Gestiona todo el sistema'),
('Mesero', 'Toma pedidos y atiende mesas'),
('Cajero', 'Procesa pagos y genera ventas');

-- ============================================
-- 2. Usuarios
-- ============================================
CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150),
    estado ENUM('Activo','Inactivo') DEFAULT 'Activo',
    id_rol INT,
    identificador BIT, 
    FOREIGN KEY (id_rol) REFERENCES Roles(id_rol)
);

INSERT INTO Usuarios (nombre_usuario, contrasena, nombre_completo, id_rol, identificador)
VALUES
('admin', '1234', 'Carlos Mendoza', 1, 1),
('mesero1', 'abcd', 'Luis Pérez', 2, 0),
('cajera1', 'xyz', 'Ana Torres', 3, 0);

-- ============================================
-- 3. Mesas
-- ============================================
CREATE TABLE Mesas (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT DEFAULT 4,
    estado ENUM('libre', 'ocupada', 'reservada') DEFAULT 'libre',
    id_mesero_asignado INT NULL,
    FOREIGN KEY (id_mesero_asignado) REFERENCES Usuarios(id_usuario)
);

INSERT INTO Mesas (numero, capacidad, estado) VALUES
(1, 4, 'libre'),
(2, 6, 'ocupada'),
(3, 2, 'reservada');

-- ============================================
-- 4. Categorias
-- ============================================
CREATE TABLE Categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

INSERT INTO Categorias (nombre) VALUES
('Entradas'),
('Platos Fuertes'),
('Bebidas');

-- ============================================
-- 5. Menu
-- ============================================
CREATE TABLE Menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    id_categoria INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES Categorias(id_categoria)
);

INSERT INTO Menu (nombre, descripcion, precio, id_categoria)
VALUES
('Ensalada César', 'Lechuga, pollo y aderezo César', 45.00, 1),
('Pasta Alfredo', 'Pasta con salsa cremosa de queso', 80.00, 2),
('Jugo de Naranja', 'Jugo natural recién exprimido', 25.00, 3);

-- ============================================
-- 6. Recetas
-- ============================================
CREATE TABLE Recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT UNIQUE NOT NULL,
    descripcion TEXT,
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu)
);

INSERT INTO Recetas (id_menu, descripcion)
VALUES
(1, 'Preparar la ensalada con pollo y aderezo.'),
(2, 'Cocinar la pasta y mezclar con salsa Alfredo.'),
(3, 'Exprimir naranjas y servir frío.');

-- ============================================
-- 7. Inventario
-- ============================================
CREATE TABLE Inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO Inventario (nombre, descripcion)
VALUES
('Cocina Principal', 'Insumos de cocina'),
('Barra', 'Bebidas y jugos'),
('Almacén General', 'Productos secos');

-- ============================================
-- 8. Comestibles
-- ============================================
CREATE TABLE Comestibles (
    id_comestible INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20),
    familia VARCHAR(50),
    nombre VARCHAR(100) NOT NULL,
    unidad VARCHAR(20) NOT NULL,
    cantidad DECIMAL(10,2) DEFAULT 0,
    coste_unidad DECIMAL(10,2) DEFAULT 0,
    valor_inventario DECIMAL(10,2) AS (cantidad * coste_unidad) STORED,
    id_inventario INT NOT NULL,
    FOREIGN KEY (id_inventario) REFERENCES Inventario(id_inventario)
);

INSERT INTO Comestibles (codigo, familia, nombre, unidad, cantidad, coste_unidad, id_inventario)
VALUES
('A001', 'Verduras', 'Lechuga', 'kg', 5, 15.00, 1),
('A002', 'Carnes', 'Pollo', 'kg', 10, 40.00, 1),
('B001', 'Frutas', 'Naranja', 'kg', 8, 20.00, 2);

-- ============================================
-- 9. Detalle_Receta
-- ============================================
CREATE TABLE Detalle_Receta (
    id_detalle_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_receta INT NOT NULL,
    id_comestible INT NOT NULL,
    cantidad_usada DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(20),
    FOREIGN KEY (id_receta) REFERENCES Recetas(id_receta),
    FOREIGN KEY (id_comestible) REFERENCES Comestibles(id_comestible)
);

INSERT INTO Detalle_Receta (id_receta, id_comestible, cantidad_usada, unidad)
VALUES
(1, 1, 0.20, 'kg'),
(1, 2, 0.10, 'kg'),
(3, 3, 0.50, 'kg');

-- ============================================
-- 10. Ventas
-- ============================================
CREATE TABLE Ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_mesa INT,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('efectivo','tarjeta','transferencia','mixto') DEFAULT 'efectivo',
    estado ENUM('pendiente','pagada','anulada') DEFAULT 'pendiente',
    FOREIGN KEY (id_mesa) REFERENCES Mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- ============================================
-- 11. Detalle_Venta
-- ============================================
CREATE TABLE Detalle_Venta (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_menu INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_venta) REFERENCES Ventas(id_venta),
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu)
);

-- ============================================
-- 12. Pedidos (actualizada)
-- ============================================
CREATE TABLE Pedidos (
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
    FOREIGN KEY (id_mesa) REFERENCES Mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- ============================================
-- 13. Detalle_Pedido
-- ============================================
CREATE TABLE Detalle_Pedido (
    id_detalle_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_menu INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_pedido) REFERENCES Pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES Menu(id_menu)
);

-- ============================================
-- 14. Facturas (actualizada con campos cliente)
-- ============================================
CREATE TABLE Facturas (
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
    FOREIGN KEY (id_venta) REFERENCES Ventas(id_venta),
    FOREIGN KEY (id_pedido) REFERENCES Pedidos(id_pedido)
);

-- ============================================
-- 15. Cuentas_Separadas
-- ============================================
CREATE TABLE Cuentas_Separadas (
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
    FOREIGN KEY (id_pedido) REFERENCES Pedidos(id_pedido)
);

-- ============================================
-- 16. Detalle_Cuenta
-- ============================================
CREATE TABLE Detalle_Cuenta (
    id_detalle_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta INT NOT NULL,
    id_detalle_pedido INT NOT NULL,
    cantidad_asignada INT NOT NULL,
    FOREIGN KEY (id_cuenta) REFERENCES Cuentas_Separadas(id_cuenta),
    FOREIGN KEY (id_detalle_pedido) REFERENCES Detalle_Pedido(id_detalle_pedido)
);
