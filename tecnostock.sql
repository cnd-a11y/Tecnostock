CREATE DATABASE IF NOT EXISTS tecnostock
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE tecnostock;

CREATE TABLE categoria (
    id_categoria INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_categoria)
) ENGINE=InnoDB;

CREATE TABLE usuario (
    id_usuario INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    clave VARCHAR(255) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY correo (correo)
) ENGINE=InnoDB;

CREATE TABLE producto (
    id_producto INT(11) NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock_actual INT(11) NOT NULL,
    stock_minimo INT(11) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    id_categoria INT(11) NOT NULL,
    PRIMARY KEY (id_producto),
    UNIQUE KEY codigo (codigo),
    KEY id_categoria (id_categoria),
    CONSTRAINT producto_ibfk_1
        FOREIGN KEY (id_categoria)
        REFERENCES categoria (id_categoria)
) ENGINE=InnoDB;

CREATE TABLE movimiento (
    id_movimiento INT(11) NOT NULL AUTO_INCREMENT,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('entrada','salida') NOT NULL,
    cantidad INT(11) NOT NULL,
    observacion VARCHAR(255) DEFAULT NULL,
    id_producto INT(11) NOT NULL,
    id_usuario INT(11) NOT NULL,
    PRIMARY KEY (id_movimiento),
    KEY id_producto (id_producto),
    KEY id_usuario (id_usuario),
    CONSTRAINT movimiento_ibfk_1
        FOREIGN KEY (id_producto)
        REFERENCES producto (id_producto),
    CONSTRAINT movimiento_ibfk_2
        FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
) ENGINE=InnoDB;

INSERT INTO categoria (id_categoria, nombre, descripcion, estado) VALUES
(1, 'Accesorios', 'Accesorios tecnologicos', 1),
(2, 'Perifericos', 'Dispositivos perifericos', 1),
(3, 'Almacenamiento', 'Dispositivos de almacenamiento', 1);

INSERT INTO usuario (id_usuario, nombre, correo, clave, estado) VALUES
(1, 'Administrador', 'admin@tecno.com', '$2y$12$oUPZhqfSYI.P/qjLw.1nKuBVmORJ0Cx4Ve/rEAm.ozeseEuLCv.be', 1);

INSERT INTO producto (id_producto, codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria) VALUES
(1, 'TEC001', 'Mouse inalámbrico', 'Mouse inalámbrico para computador', 12990.00, 7, 5, 1, 2),
(2, 'TEC002', 'Teclado USB', 'Teclado USB para computador', 9990.00, 12, 5, 1, 2),
(3, 'TEC003', 'Cable USB-C', 'Cable de conexión USB-C', 4990.00, 20, 5, 1, 1),
(4, 'TEC004', 'Memoria USB 64 GB', 'Memoria USB de almacenamiento', 7990.00, 1, 5, 1, 3),
(5, 'TEC005', 'Audífonos inalámbricos', 'Audífonos inalámbricos para audio', 19990.00, 11, 3, 0, 1);

INSERT INTO movimiento (id_movimiento, fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES
(1, '2026-11-23 16:29:48', 'entrada', 3, 'llego 3 productos nuevos', 5, 1),
(2, '2026-11-23 16:30:22', 'salida', 2, 'se vendieron 2 productos', 4, 1),
(3, '2026-11-23 16:31:25', 'salida', 8, 'se vendieron 8 productos', 1, 1);
