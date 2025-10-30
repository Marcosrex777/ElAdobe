-- ============================================================
-- 🧾 BASE DE DATOS: el_adobe
-- Estructura general del sistema
-- ============================================================

DROP DATABASE IF EXISTS el_adobe;
CREATE DATABASE el_adobe;
USE el_adobe;

-- ============================================================
-- 1️⃣ TABLA: PROVEEDORES
-- ============================================================
CREATE TABLE proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre_proveedor VARCHAR(100) NOT NULL,
    persona_contacto VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion VARCHAR(150),
    categoria VARCHAR(50),
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo'
);

CREATE TABLE compras_proveedores (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    nombre_proveedor VARCHAR(100) NOT NULL,
    fecha_compra DATE NOT NULL,
    numero_comprobante VARCHAR(50),
    metodo_pago ENUM('Efectivo', 'Transferencia', 'Tarjeta', 'Cheque') DEFAULT 'Efectivo',
    subtotal DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL
);

CREATE TABLE detalle_compra (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    producto VARCHAR(100) NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (id_compra) REFERENCES compras_proveedores(id_compra)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- ============================================================
-- 2️⃣ TABLA: ROLES
-- ============================================================
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(100) NOT NULL,
    descripcion TEXT
);

-- ============================================================
-- 3️⃣ TABLA: USUARIOS
-- ============================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(150),
    estado ENUM('Activo','Inactivo') DEFAULT 'Activo',
    id_rol INT,
    identificador TINYINT(1),
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- ============================================================
-- 4️⃣ TABLA: MESAS
-- ============================================================
CREATE TABLE mesas (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT DEFAULT 4,
    estado ENUM('libre', 'ocupada', 'reservada') DEFAULT 'libre',
    id_mesero_asignado INT NULL,
    CONSTRAINT fk_mesas_usuarios
        FOREIGN KEY (id_mesero_asignado) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);
-- ============================================================
-- 2️⃣ SECCIÓN: CATEGORÍAS, MENÚ Y RECETAS
-- ============================================================

-- ============================================================
-- 2.1 TABLA: CATEGORÍAS
-- ============================================================
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

-- ============================================================
-- 2.2 TABLA: MENÚ (PRODUCTOS / PLATILLOS)
-- ============================================================
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    id_categoria INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_menu_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- ============================================================
-- 2.3 TABLA: RECETAS (1 RECETA POR PRODUCTO)
-- ============================================================
CREATE TABLE recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_menu INT NOT NULL UNIQUE,
    descripcion TEXT,
    CONSTRAINT fk_recetas_menu
        FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
-- ============================================================
-- 3️⃣ SECCIÓN: INVENTARIO DE COMESTIBLES Y RECETAS DETALLADAS
-- ============================================================

-- ============================================================
-- 3.1 TABLA: REGISTRO DE COMESTIBLES (HISTORIAL DE ENTRADAS)
-- ============================================================
CREATE TABLE registro_comestibles (
    id_registro INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30),
    categoria VARCHAR(50),
    nombre VARCHAR(100),
    unidad_medida VARCHAR(20),
    coste DECIMAL(10,2),
    cantidad DECIMAL(10,2),
    fecha_actualizacion DATE DEFAULT (CURRENT_DATE),
    id_proveedor INT,
    CONSTRAINT fk_registro_comestibles_proveedor
        FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- ============================================================
-- 3.2 TABLA: INVENTARIO DE COMESTIBLES (RESUMEN PRINCIPAL)
-- ============================================================
CREATE TABLE inventario_comestibles (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE,
    categoria VARCHAR(50),
    nombre VARCHAR(100),
    unidad_medida VARCHAR(20),
    coste DECIMAL(10,2),
    cantidad_total DECIMAL(10,2),
    coste_inventario DECIMAL(10,2)
        GENERATED ALWAYS AS (cantidad_total * coste) STORED,
    fecha_primera DATE,
    fecha_actualizacion DATE DEFAULT (CURRENT_DATE)
);

-- ============================================================
-- 3.3 TABLA: DETALLE DE RECETAS (INSUMOS UTILIZADOS)
-- ============================================================
CREATE TABLE detalle_receta (
    id_detalle_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_receta INT NOT NULL,
    id_comestible INT NOT NULL,
    cantidad_usada DECIMAL(10,2) NOT NULL,
    unidad VARCHAR(20),
    CONSTRAINT fk_detalle_receta_recetas
        FOREIGN KEY (id_receta) REFERENCES recetas(id_receta)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_receta_inventario
        FOREIGN KEY (id_comestible) REFERENCES inventario_comestibles(id_inventario)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);
-- ============================================================
-- 4️⃣ SECCIÓN: MOVIMIENTOS Y RETIROS DE COMESTIBLES
-- ============================================================

-- ============================================================
-- 4.1 TABLA: MOVIMIENTOS DE COMESTIBLES (ENTRADAS Y SALIDAS)
-- ============================================================
CREATE TABLE movimientos_consumibles (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    tipo ENUM('Entrada', 'Salida') NOT NULL,
    motivo VARCHAR(150),
    cantidad DECIMAL(10,2) NOT NULL,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 4.2 TABLA: RETIROS DE COMESTIBLES (BAJAS POR DAÑO O CADUCIDAD)
-- ============================================================
CREATE TABLE retiros_consumibles (
    id_retiro INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    motivo ENUM('Caducado', 'Roto', 'Mal estado', 'Inservible') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    fecha_retiro DATETIME DEFAULT CURRENT_TIMESTAMP
);
-- ============================================================
-- 5️⃣ SECCIÓN: MOBILIARIO Y EQUIPO
-- ============================================================

-- ============================================================
-- 5.1 TABLA: REGISTRO DE MOBILIARIO Y EQUIPO (HISTORIAL)
-- ============================================================
CREATE TABLE registro_mobiliario_equipo (
    id_registro INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30),
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('Mobiliario','Equipo') NOT NULL,
    cantidad INT DEFAULT 1,
    costo_unidad DECIMAL(10,2),
    estado ENUM('Activo','En reparación','Dado de baja') DEFAULT 'Activo',
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT NULL,
    id_proveedor INT,
    CONSTRAINT fk_regmob_prov
        FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- ============================================================
-- 5.2 TABLA: INVENTARIO DE MOBILIARIO Y EQUIPO (RESUMEN)
-- ============================================================
CREATE TABLE inventario_mobiliario_equipo (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('Mobiliario','Equipo') NOT NULL,
    cantidad_total INT DEFAULT 0,
    costo_unidad DECIMAL(10,2),
    estado ENUM('Activo','En reparación','Dado de baja') DEFAULT 'Activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 5.3 TABLA: RETIROS DE MOBILIARIO Y EQUIPO
-- ============================================================
CREATE TABLE retiros_mobiliario_equipo (
    id_retiro INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('Mobiliario','Equipo') NOT NULL,
    cantidad INT NOT NULL,
    motivo TEXT NOT NULL,
    estado_post_retiro ENUM('Reparación','Baja definitiva','Traslado') DEFAULT 'Reparación',
    fecha_retiro DATETIME DEFAULT CURRENT_TIMESTAMP,
    responsable VARCHAR(100),
    observaciones TEXT,
    CONSTRAINT fk_ret_mob_inv
        FOREIGN KEY (codigo) REFERENCES inventario_mobiliario_equipo(codigo)
        ON UPDATE CASCADE
);
-- ============================================================
-- 6️⃣ SECCIÓN: VENTAS, PEDIDOS Y FACTURAS
-- ============================================================

-- ============================================================
-- 6.1 TABLA: VENTAS
-- ============================================================
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_mesa INT NULL,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) DEFAULT 0,
    metodo_pago ENUM('efectivo','tarjeta','transferencia','mixto') DEFAULT 'efectivo',
    estado ENUM('pendiente','pagada','anulada') DEFAULT 'pendiente',
    CONSTRAINT fk_ventas_mesas
        FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa)
        ON UPDATE CASCADE,
    CONSTRAINT fk_ventas_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
);

-- ============================================================
-- 6.2 TABLA: DETALLE DE VENTA
-- ============================================================
CREATE TABLE detalle_venta (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_menu INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    CONSTRAINT fk_detventa_venta
        FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_detventa_menu
        FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE
);

-- ============================================================
-- 6.3 TABLA: PEDIDOS
-- ============================================================
CREATE TABLE pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente','enviado','preparando','listo','entregado','finalizado','facturado','cancelado') DEFAULT 'pendiente',
    total DECIMAL(10,2) DEFAULT 0,
    CONSTRAINT fk_pedidos_mesas
        FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa)
        ON UPDATE CASCADE,
    CONSTRAINT fk_pedidos_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
);

-- ============================================================
-- 6.4 TABLA: DETALLE DE PEDIDO
-- ============================================================
CREATE TABLE detalle_pedido (
    id_detalle_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_menu INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    CONSTRAINT fk_detpedido_pedidos
        FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_detpedido_menu
        FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
        ON UPDATE CASCADE
);

-- ============================================================
-- 6.5 TABLA: FACTURAS
-- ============================================================
CREATE TABLE facturas (
    id_factura INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NULL,
    id_pedido INT NULL,
    numero_factura VARCHAR(20) UNIQUE,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,2),
    iva DECIMAL(10,2) DEFAULT 0,
    propina DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2),
    cliente_nombre VARCHAR(255),
    cliente_nit VARCHAR(20),
    direccion TEXT,
    metodo_pago ENUM('efectivo','tarjeta','transferencia','mixto'),
    CONSTRAINT chk_factura_rel
        CHECK ((id_venta IS NOT NULL) OR (id_pedido IS NOT NULL)),
    CONSTRAINT fk_fact_venta
        FOREIGN KEY (id_venta) REFERENCES ventas(id_venta)
        ON UPDATE CASCADE,
    CONSTRAINT fk_fact_pedido
        FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
        ON UPDATE CASCADE
);
-- ============================================================
-- 7️⃣ SECCIÓN: TRIGGERS AUTOMÁTICOS
-- ============================================================

DELIMITER $$

-- ============================================================
-- 7.1 TRIGGER: ACTUALIZAR INVENTARIO DE COMESTIBLES AL REGISTRAR COMPRA
-- ============================================================
CREATE TRIGGER trg_actualizar_inventario_comestibles
AFTER INSERT ON registro_comestibles
FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM inventario_comestibles WHERE codigo = NEW.codigo) THEN
        UPDATE inventario_comestibles
        SET cantidad_total = cantidad_total + NEW.cantidad,
            coste = GREATEST(coste, NEW.coste),
            fecha_actualizacion = CURRENT_DATE
        WHERE codigo = NEW.codigo;
    ELSE
        INSERT INTO inventario_comestibles
            (codigo, categoria, nombre, unidad_medida, coste, cantidad_total, fecha_primera, fecha_actualizacion)
        VALUES
            (NEW.codigo, NEW.categoria, NEW.nombre, NEW.unidad_medida, NEW.coste, NEW.cantidad, NEW.fecha_actualizacion, CURRENT_DATE);
    END IF;
END$$

-- ============================================================
-- 7.2 TRIGGER: ACTUALIZA INVENTARIO DE MOBILIARIO AL REGISTRAR NUEVO EQUIPO
-- ============================================================
CREATE TRIGGER trg_actualizar_inventario_mobiliario
AFTER INSERT ON registro_mobiliario_equipo
FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM inventario_mobiliario_equipo WHERE codigo = NEW.codigo) THEN
        UPDATE inventario_mobiliario_equipo
        SET 
            cantidad_total = cantidad_total + NEW.cantidad,
            costo_unidad = GREATEST(COALESCE(costo_unidad,0), COALESCE(NEW.costo_unidad,0)),
            estado = NEW.estado,
            fecha_actualizacion = CURRENT_TIMESTAMP
        WHERE codigo = NEW.codigo;
    ELSE
        INSERT INTO inventario_mobiliario_equipo
            (codigo, nombre, tipo, cantidad_total, costo_unidad, estado)
        VALUES
            (NEW.codigo, NEW.nombre, NEW.tipo, NEW.cantidad, NEW.costo_unidad, NEW.estado);
    END IF;
END$$

-- ============================================================
-- 7.3 TRIGGER: ACTUALIZA INVENTARIO DE COMESTIBLES POR MOVIMIENTOS (ENTRADAS / SALIDAS)
-- ============================================================
CREATE TRIGGER trg_movimiento_inventario_comestibles
AFTER INSERT ON movimientos_consumibles
FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM inventario_comestibles WHERE codigo = NEW.codigo) THEN
        IF NEW.tipo = 'Entrada' THEN
            UPDATE inventario_comestibles
            SET cantidad_total = cantidad_total + NEW.cantidad,
                fecha_actualizacion = CURRENT_DATE
            WHERE codigo = NEW.codigo;
        ELSEIF NEW.tipo = 'Salida' THEN
            UPDATE inventario_comestibles
            SET cantidad_total = GREATEST(0, cantidad_total - NEW.cantidad),
                fecha_actualizacion = CURRENT_DATE
            WHERE codigo = NEW.codigo;
        END IF;
    END IF;
END$$

-- ============================================================
-- 7.4 TRIGGER: RETIRO AUTOMÁTICO DE COMESTIBLES
-- ============================================================
CREATE TRIGGER trg_retiro_inventario_comestibles
AFTER INSERT ON retiros_consumibles
FOR EACH ROW
BEGIN
    IF EXISTS (SELECT 1 FROM inventario_comestibles WHERE codigo = NEW.codigo) THEN
        UPDATE inventario_comestibles
        SET cantidad_total = GREATEST(0, cantidad_total - NEW.cantidad),
            fecha_actualizacion = CURRENT_DATE
        WHERE codigo = NEW.codigo;
    END IF;
END$$

-- ============================================================
-- 7.5 TRIGGER: DESCUENTO AUTOMÁTICO POR PEDIDO
-- ============================================================
CREATE TRIGGER trg_descuento_inventario_pedido
AFTER INSERT ON detalle_pedido
FOR EACH ROW
BEGIN
    DECLARE v_id_comestible INT;
    DECLARE v_cant_usada DECIMAL(10,2);
    DECLARE done INT DEFAULT 0;

    DECLARE cur CURSOR FOR
        SELECT dr.id_comestible, dr.cantidad_usada
        FROM recetas r
        INNER JOIN detalle_receta dr ON r.id_receta = dr.id_receta
        WHERE r.id_menu = NEW.id_menu;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_id_comestible, v_cant_usada;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Verificar stock suficiente
        IF (SELECT cantidad_total FROM inventario_comestibles WHERE id_inventario = v_id_comestible) < (v_cant_usada * NEW.cantidad) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No hay suficiente inventario para este pedido.';
        END IF;

        -- Descontar del inventario
        UPDATE inventario_comestibles
        SET cantidad_total = cantidad_total - (v_cant_usada * NEW.cantidad),
            fecha_actualizacion = CURRENT_DATE
        WHERE id_inventario = v_id_comestible;

        -- Registrar movimiento
        INSERT INTO movimientos_consumibles (codigo, tipo, motivo, cantidad)
        SELECT ic.codigo, 'Salida', CONCAT('Consumo por pedido #', NEW.id_pedido), (v_cant_usada * NEW.cantidad)
        FROM inventario_comestibles ic
        WHERE ic.id_inventario = v_id_comestible;
    END LOOP;
    CLOSE cur;
END$$

-- ============================================================
-- 7.6 TRIGGER: DESCUENTO AUTOMÁTICO POR VENTA
-- ============================================================
CREATE TRIGGER trg_descuento_inventario_venta
AFTER INSERT ON detalle_venta
FOR EACH ROW
BEGIN
    DECLARE v_id_comestible INT;
    DECLARE v_cant_usada DECIMAL(10,2);
    DECLARE done INT DEFAULT 0;

    DECLARE cur CURSOR FOR
        SELECT dr.id_comestible, dr.cantidad_usada
        FROM recetas r
        INNER JOIN detalle_receta dr ON r.id_receta = dr.id_receta
        WHERE r.id_menu = NEW.id_menu;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO v_id_comestible, v_cant_usada;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Validar stock
        IF (SELECT cantidad_total FROM inventario_comestibles WHERE id_inventario = v_id_comestible) < (v_cant_usada * NEW.cantidad) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No hay suficiente inventario para esta venta.';
        END IF;

        -- Descontar inventario
        UPDATE inventario_comestibles
        SET cantidad_total = cantidad_total - (v_cant_usada * NEW.cantidad),
            fecha_actualizacion = CURRENT_DATE
        WHERE id_inventario = v_id_comestible;

        -- Registrar salida en movimientos
        INSERT INTO movimientos_consumibles (codigo, tipo, motivo, cantidad)
        SELECT ic.codigo, 'Salida', CONCAT('Consumo por venta #', NEW.id_venta), (v_cant_usada * NEW.cantidad)
        FROM inventario_comestibles ic
        WHERE ic.id_inventario = v_id_comestible;
    END LOOP;
    CLOSE cur;
END$$

-- ============================================================
-- 7.7 TRIGGER: RETIRO DE MOBILIARIO / EQUIPO
-- ============================================================
CREATE TRIGGER trg_retiro_mobiliario_equipo
AFTER INSERT ON retiros_mobiliario_equipo
FOR EACH ROW
BEGIN
    UPDATE inventario_mobiliario_equipo
    SET cantidad_total = GREATEST(0, cantidad_total - NEW.cantidad),
        estado = CASE 
                   WHEN NEW.estado_post_retiro = 'Reparación' THEN 'En reparación'
                   WHEN NEW.estado_post_retiro = 'Baja definitiva' THEN 'Dado de baja'
                   ELSE estado
                 END,
        fecha_actualizacion = CURRENT_TIMESTAMP
    WHERE codigo = NEW.codigo;
END$$

DELIMITER ;
