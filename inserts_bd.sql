-- 1. Insertar Roles
INSERT INTO roles (nombre) VALUES 
('Administrador');

-- 2. Insertar Usuario Administrador
INSERT INTO usuarios (
    nombre,
    email,
    password,
    rol_id
) VALUES (
    'Admin Principal',
    'admin@example.com',
    '5f4dcc3b5aa765d61d8327deb882cf99', -- Contraseña "password" (SHA-1 para ejemplo, usa SHA2 en producción)
    1
);

-- 3. Insertar Clientes
INSERT INTO clientes (nombre, direccion, telefono, correo) VALUES 
('XTB', 'NY, Piscataway, NJ', '555-1234', 'xtb@example.com'),
('Cliente B', 'Ciudad Arce, El Salvador', '555-5678', 'cliente_b@example.com');

-- 4. Insertar Items
INSERT INTO items (item_numero, item_nombre, item_dir_specs) VALUES 
('25T4884MX', 'Vestido de niña floreado', '/specs/25T4884MX.pdf'),
('35T4884MX', 'Pantalón casual', '/specs/35T4884MX.pdf');

-- 5. Insertar Procesos de Producción
INSERT INTO procesos_produccion (nombre, descripcion) VALUES 
('Corte', 'Proceso de corte de tela'),
('Costura', 'Proceso de confección'),
('Control de Calidad', 'Inspección final de prendas');

-- 6. Insertar POs
INSERT INTO po (
    numero_po,
    fecha_creacion,
    fecha_entrega_estimada,
    estado,
    po_total_unidades,
    po_total_valor,
    po_cliente_id
) VALUES 
(
    'D-13219-M',
    '2024-09-30',
    '2025-02-25',
    'confirmed',
    1800,
    4184.40,
    1 -- ID del cliente XTB
),
(
    'PO-002',
    '2023-10-02',
    '2023-10-20',
    'pendiente',
    500,
    1200.00,
    2 -- ID del cliente B
);

-- 7. Insertar Detalles de PO
-- Detalles para PO D-13219-M
INSERT INTO po_detalle (
    po_id,
    po_detalle_item,
    po_detalle_talla,
    po_detalle_cant_piezas_total,
    po_detalle_pcs_carton,
    po_detalle_pcs_poly,
    po_detalle_estado
) VALUES 
(
    1, -- ID de la PO D-13219-M
    1, -- ID del item "Vestido de niña floreado"
    '2T',
    240,
    24,
    1,
    'confirmed'
),
(
    1,
    1,
    '3T',
    336,
    24,
    1,
    'confirmed'
),
(
    1,
    1,
    '4T',
    384,
    24,
    1,
    'confirmed'
),
(
    1,
    2, -- ID del item "Pantalón casual"
    '5',
    360,
    24,
    1,
    'confirmed'
),
(
    1,
    2,
    '6',
    264,
    24,
    1,
    'confirmed'
),
(
    1,
    2,
    '7-8',
    216,
    24,
    1,
    'confirmed'
);

-- Detalles para PO-002
INSERT INTO po_detalle (
    po_id,
    po_detalle_item,
    po_detalle_talla,
    po_detalle_cant_piezas_total,
    po_detalle_pcs_carton,
    po_detalle_pcs_poly,
    po_detalle_estado
) VALUES 
(
    2, -- ID de la PO-002
    2, -- ID del item "Pantalón casual"
    'L',
    150,
    30,
    1,
    'pendiente'
);

-- 8. Insertar Tipos de Pruebas
INSERT INTO tipos_pruebas (nombre, descripcion, requisito) VALUES 
('Lead Testing', 'Prueba de plomo en telas y estampados', 'Menos de 30PPM'),
('CPSIA - Phthalates', 'Cumplir normas CPSIA para ftalatos', 'Aprobado por laboratorio'),
('Flammability', 'Prueba de inflamabilidad', 'CFR Title 16, Part 1610, Class 1');

-- 9. Insertar Pruebas de Calidad para POs
INSERT INTO pruebas_calidad (po_id, tipo_prueba_id, estado) VALUES 
(1, 1, 'pendiente'), -- Lead Testing para PO D-13219-M
(1, 2, 'pendiente'), -- CPSIA - Phthalates para PO D-13219-M
(1, 3, 'pendiente'), -- Flammability para PO D-13219-M
(2, 1, 'pendiente'); -- Lead Testing para PO-002

-- 10. Insertar Resultados de Pruebas
INSERT INTO resultados_pruebas (
    prueba_calidad_id,
    po_detalle_id,
    resultado,
    observaciones,
    archivo_rubrica
) VALUES 
(
    1, -- ID de la prueba Lead Testing para PO D-13219-M
    1, -- ID del detalle de PO (Vestido 2T)
    'aprobado',
    'Cumple con el requisito de plomo',
    '/rubricas/lead_testing_25T4884MX_2T.pdf'
),
(
    1,
    2, -- ID del detalle de PO (Vestido 3T)
    'rechazado',
    'Excede el límite de plomo',
    '/rubricas/lead_testing_25T4884MX_3T.pdf'
);

-- 11. Insertar Aprobaciones de PO
INSERT INTO aprobaciones_po (
    po_id,
    usuario_aprobador_id,
    fecha_aprobacion,
    estado,
    comentario
) VALUES 
(
    1, -- PO D-13219-M
    1, -- Usuario administrador
    '2024-09-30',
    'aprobada',
    'PO confirmada por el cliente XTB'
);

-- 12. Insertar Modificaciones de PO
INSERT INTO modificaciones_po (
    po_id,
    usuario_modificacion_id,
    fecha_modificacion,
    campo_modificado,
    valor_anterior,
    valor_nuevo
) VALUES 
(
    1,
    1,
    '2024-11-27 10:30:00',
    'po_estado',
    'pendiente',
    'confirmed'
);