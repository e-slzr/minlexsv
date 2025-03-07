-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS minlex_elsalvador;
USE minlex_elsalvador;

-- Tabla: roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roles_nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuarios_nombre VARCHAR(100) NOT NULL,
    usuarios_email VARCHAR(100) NOT NULL UNIQUE,
    usuarios_password VARCHAR(255) NOT NULL,
    usuarios_rol_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuarios_rol_id) REFERENCES roles(id)
);

-- Tabla: clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clientes_nombre VARCHAR(100) NOT NULL,
    clientes_direccion VARCHAR(255),
    clientes_telefono VARCHAR(20),
    clientes_correo VARCHAR(100)
);

-- Tabla: items
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_numero VARCHAR(50) NOT NULL UNIQUE,
    item_nombre VARCHAR(100) NOT NULL,
    item_dir_specs VARCHAR(255)
);

-- Tabla: procesos_produccion
CREATE TABLE procesos_produccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procesos_produccion_nombre VARCHAR(100) NOT NULL,
    procesos_produccion_descripcion TEXT
);

-- Tabla: po
CREATE TABLE po (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_numero VARCHAR(50) NOT NULL UNIQUE,
    po_fecha_creacion DATE NOT NULL,
    po_fecha_entrega_estimada DATE,
    po_estado ENUM('pendiente', 'en_proceso', 'completada', 'cancelada', 'confirmed') DEFAULT 'pendiente',
    po_total_unidades INT NOT NULL,
    po_total_valor DECIMAL(10, 2) NOT NULL,
    po_cliente_id INT NOT NULL,
    FOREIGN KEY (po_cliente_id) REFERENCES clientes(id)
);

-- Tabla: po_detalle
CREATE TABLE po_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    po_detalle_item INT NOT NULL,
    po_detalle_talla VARCHAR(10) NOT NULL,
    po_detalle_cant_piezas_total INT NOT NULL,
    po_detalle_pcs_carton INT NOT NULL,
    po_detalle_pcs_poly INT NOT NULL,
    po_detalle_estado ENUM('pendiente', 'en_proceso', 'completado', 'confirmed') DEFAULT 'pendiente',
    FOREIGN KEY (po_id) REFERENCES po(id),
    FOREIGN KEY (po_detalle_item) REFERENCES items(id)
);

-- Tabla: po_detalle_cantidades
CREATE TABLE po_detalle_cantidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_detalle_id INT NOT NULL,
    po_detalle_cantidades_nombre_proceso VARCHAR(100) NOT NULL,
    po_detalle_cantidades_cant INT DEFAULT 0,
    FOREIGN KEY (po_detalle_id) REFERENCES po_detalle(id)
);

-- Tabla: ordenes_produccion
CREATE TABLE ordenes_produccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordenes_produccion_id_po_detalle INT NOT NULL,
    ordenes_produccion_id_proceso INT NOT NULL,
    orden_produccion_fecha_inicio DATE,
    orden_produccion_fecha_fin DATE,
    orden_produccion_estado ENUM('pendiente', 'en_proceso', 'completado') DEFAULT 'pendiente',
    orden_produccion_cantidad_asignada INT NOT NULL,
    orden_produccion_cantidad_completada INT DEFAULT 0,
    FOREIGN KEY (ordenes_produccion_id_po_detalle) REFERENCES po_detalle(id),
    FOREIGN KEY (ordenes_produccion_id_proceso) REFERENCES procesos_produccion(id)
);

-- Tabla: tipos_pruebas
CREATE TABLE tipos_pruebas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    requisito VARCHAR(255)
);

-- Tabla: pruebas_calidad
CREATE TABLE pruebas_calidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    tipo_prueba_id INT NOT NULL,
    estado ENUM('pendiente', 'completado', 'rechazado') DEFAULT 'pendiente',
    FOREIGN KEY (po_id) REFERENCES po(id),
    FOREIGN KEY (tipo_prueba_id) REFERENCES tipos_pruebas(id)
);

-- Tabla: resultados_pruebas
CREATE TABLE resultados_pruebas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prueba_calidad_id INT NOT NULL,
    po_detalle_id INT NOT NULL,
    resultado ENUM('aprobado', 'rechazado') NOT NULL,
    observaciones TEXT,
    archivo_rubrica VARCHAR(255),
    FOREIGN KEY (prueba_calidad_id) REFERENCES pruebas_calidad(id),
    FOREIGN KEY (po_detalle_id) REFERENCES po_detalle(id)
);

-- Tabla: aprobaciones_po
CREATE TABLE aprobaciones_po (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    usuario_aprobador_id INT NOT NULL,
    fecha_aprobacion DATE NOT NULL,
    estado ENUM('aprobada', 'rechazada') NOT NULL,
    comentario TEXT,
    FOREIGN KEY (po_id) REFERENCES po(id),
    FOREIGN KEY (usuario_aprobador_id) REFERENCES usuarios(id)
);

-- Tabla: modificaciones_po
CREATE TABLE modificaciones_po (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    usuario_modificacion_id INT NOT NULL,
    fecha_modificacion DATETIME NOT NULL,
    campo_modificado VARCHAR(100) NOT NULL,
    valor_anterior TEXT,
    valor_nuevo TEXT,
    FOREIGN KEY (po_id) REFERENCES po(id),
    FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id)
);

-- Crear un índice simple
CREATE INDEX idx_po_numero ON po(po_numero);
