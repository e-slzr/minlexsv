-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS minlex_elsalvador;
USE minlex_elsalvador;

-- Tabla: roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_nombre VARCHAR(25) NOT NULL UNIQUE,
    rol_descripcion TEXT,
    estado ENUM('Activo', 'Deshabilitado') DEFAULT 'Activo'
);

-- Tabla: usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_alias VARCHAR(25) NOT NULL UNIQUE,
    usuario_nombre VARCHAR(25) NOT NULL,
    usuario_apellido VARCHAR(25) NOT NULL,
    usuario_password VARCHAR(255) NOT NULL,
    usuario_rol_id INT NOT NULL,
    usuario_departamento VARCHAR(25),  -- Columna correcta
    estado ENUM('Activo', 'Deshabilitado') DEFAULT 'Activo',
    usuario_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_rol_id) REFERENCES roles(id)
);

-- Tabla: clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_empresa VARCHAR(50) NOT NULL,
    cliente_nombre VARCHAR(50) NOT NULL,
    cliente_apellido VARCHAR(50) NOT NULL,
    cliente_direccion TEXT,
    cliente_telefono VARCHAR(20),
    cliente_correo VARCHAR(100)
);

-- Tabla: items
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_numero VARCHAR(50) NOT NULL UNIQUE,
    item_nombre VARCHAR(100) NOT NULL,
    item_descripcion TEXT,
    item_talla VARCHAR(10),
    item_img TEXT,
    -- item_materia_prima INT NOT NULL,
    item_dir_specs VARCHAR(255)
);

-- Tabla: procesos_produccion
CREATE TABLE procesos_produccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pp_nombre VARCHAR(100) NOT NULL,
    pp_descripcion TEXT,
    pp_costo DECIMAL(10, 2)
);

-- Tabla: po
CREATE TABLE po (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_numero VARCHAR(50) NOT NULL UNIQUE,
    po_fecha_creacion DATE NOT NULL,
    po_fecha_inicio_produccion DATE,
    po_fecha_fin_produccion DATE,
    po_fecha_envio_programada DATE,
    po_estado ENUM('Pendiente', 'En proceso', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
    po_id_cliente INT NOT NULL,
    po_id_usuario_creacion INT NOT NULL,
    po_tipo_envio ENUM('Tipo 1', 'Tipo 2', 'Tipo 3') DEFAULT 'Tipo 1',
    po_comentario TEXT,
    po_notas TEXT,
    FOREIGN KEY (po_id_cliente) REFERENCES clientes(id),
    FOREIGN KEY (po_id_usuario_creacion) REFERENCES usuarios(id)
);

-- Tabla: po_detalle
CREATE TABLE po_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pd_id_po INT NOT NULL,
    pd_item INT NOT NULL,
    pd_cant_piezas_total INT NOT NULL,
    pd_pcs_carton INT NOT NULL,
    pd_pcs_poly INT NOT NULL,
    pd_estado ENUM('Pendiente', 'En proceso', 'Completado') DEFAULT 'Pendiente',
    pd_precio_unitario DECIMAL(10, 2),
    FOREIGN KEY (pd_id_po) REFERENCES po(id),
    FOREIGN KEY (pd_item) REFERENCES items(id)
);

-- Tabla: po_detalle_cantidades
CREATE TABLE po_detalle_cantidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pdc_id_po_detalle INT NOT NULL,
    pdc_cant_piezas INT NOT NULL,
    pdc_estado ENUM('Pendiente', 'En proceso', 'Completado') DEFAULT 'Pendiente',
    pdc_precio_unitario DECIMAL(10, 2),
    FOREIGN KEY (pdc_id_po_detalle) REFERENCES po_detalle(id)
);

-- Tabla: ordenes_produccion
CREATE TABLE ordenes_produccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    op_id_pd INT NOT NULL,
    op_operador_asignado INT NOT NULL,
    op_id_proceso INT NOT NULL,
    op_fecha_aprobacion DATE,
    op_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    op_fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    op_fecha_inicio DATE,
    op_fecha_fin DATE,
    op_estado ENUM('Pendiente', 'En proceso', 'Completado') DEFAULT 'Pendiente',
    op_cantidad_asignada INT NOT NULL,
    op_cantidad_completada INT DEFAULT 0,
    op_comentario TEXT,
    FOREIGN KEY (op_id_pd) REFERENCES po_detalle(id),
    FOREIGN KEY (op_id_proceso) REFERENCES procesos_produccion(id),
    FOREIGN KEY (op_operador_asignado) REFERENCES usuarios(id)
);

-- Tabla: tipos_pruebas
CREATE TABLE tipos_pruebas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tp_nombre VARCHAR(100) NOT NULL UNIQUE,
    tp_descripcion TEXT,
    tp_requisito VARCHAR(255)
);

-- Tabla: pruebas_calidad
CREATE TABLE pruebas_calidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id_po INT NOT NULL,
    pc_id_tipo_prueba INT NOT NULL,
    pc_estado ENUM('Pendiente', 'Completado', 'Rechazado') DEFAULT 'Pendiente',
    FOREIGN KEY (pc_id_po) REFERENCES po(id),
    FOREIGN KEY (pc_id_tipo_prueba) REFERENCES tipos_pruebas(id)
);

-- Tabla: resultados_pruebas
CREATE TABLE resultados_pruebas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rp_prueba_calidad_id INT NOT NULL,
    rp_po_detalle_id INT NOT NULL,
    rp_resultado ENUM('Aprobado', 'Rechazado') NOT NULL,
    rp_observaciones VARCHAR(255),
    rp_archivo_rubrica VARCHAR(255),
    FOREIGN KEY (rp_prueba_calidad_id) REFERENCES pruebas_calidad(id),
    FOREIGN KEY (rp_po_detalle_id) REFERENCES po_detalle(id)
);

-- Tabla: aprobaciones_po
CREATE TABLE aprobaciones_po (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ap_po_id INT NOT NULL,
    ap_usuario_aprobador INT NOT NULL,
    ap_fecha_aprobacion DATE NOT NULL,
    ap_estado ENUM('Pendiente', 'Aprobada', 'Rechazada') NOT NULL,
    ap_comentario TEXT,
    FOREIGN KEY (ap_po_id) REFERENCES po(id),
    FOREIGN KEY (ap_usuario_aprobador) REFERENCES usuarios(id)
);

-- Tabla: modificaciones_po
CREATE TABLE modificaciones_po (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mp_po_id INT NOT NULL,
    mp_usuario_modificacion INT NOT NULL,
    mp_fecha_modificacion DATETIME NOT NULL,
    mp_campo_modificado VARCHAR(100) NOT NULL,
    mp_valor_anterior TEXT,
    mp_valor_nuevo TEXT,
    FOREIGN KEY (mp_po_id) REFERENCES po(id),
    FOREIGN KEY (mp_usuario_modificacion) REFERENCES usuarios(id)
);

-- Crear un índice simple
CREATE INDEX idx_po_numero ON po(po_numero);
