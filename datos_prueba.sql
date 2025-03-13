-- 1. Insertar Roles
INSERT INTO roles (rol_nombre, rol_descripcion) VALUES 
('Administrador', 'Usuario con acceso total al sistema'),
('Operador', 'Usuario responsable de procesos de producción'),
('Calidad', 'Usuario encargado de pruebas y aprobaciones');

-- 2. Insertar Usuarios
INSERT INTO usuarios (
    usuario_nombre,
    usuario_apellido,
    usuario_password,
    usuario_rol_id,
    usuario_departamento
) VALUES 
(
    'Admin',
    'Principal',
    '5f4dcc3b5aa765d61d8327deb882cf99', -- Contraseña "password" (SHA-1, usa SHA2 o bcrypt en producción)
    1, -- Rol Administrador
    'Administración'
),
(
    'Juan',
    'Pérez',
    '5f4dcc3b5aa765d61d8327deb882cf99', -- Contraseña "password"
    2, -- Rol Operador
    'Producción'
),
(
    'María',
    'Gómez',
    '5f4dcc3b5aa765d61d8327deb882cf99', -- Contraseña "password"
    3, -- Rol Calidad
    'Control de Calidad'
);

-- 3. Insertar Clientes
INSERT INTO clientes (
    cliente_empresa,
    cliente_nombre,
    cliente_apellido,
    cliente_direccion,
    cliente_telefono,
    cliente_correo
) VALUES 
(
    'XTB',
    'Carlos',
    'López',
    'NY, Piscataway, NJ',
    '555-1234',
    'carlos.lopez@xtb.com'
),
(
    'Cliente B',
    'Laura',
    'Martínez',
    'Ciudad Arce, El Salvador',
    '555-5678',
    'laura.martinez@cliente-b.com'
);

-- 4. Insertar Items
INSERT INTO items (
    item_numero,
    item_nombre,
    item_descripcion,
    item_talla,
    item_img,
    item_dir_specs
) VALUES 
(
    '25T4884MX',
    'Vestido de niña floreado',
    'Vestido infantil estampado',
    '2T',
    '/imagenes/25T4884MX.jpg',
    '/specs/25T4884MX.pdf'
),
(
    '35T4884MX',
    'Pantalón casual',
    'Pantalón de tela ligera',
    'L',
    '/imagenes/35T4884MX.jpg',
    '/specs/35T4884MX.pdf'
);

-- 5. Insertar POs
INSERT INTO po (
    po_numero,
    po_fecha_creacion,
    po_fecha_inicio_produccion,
    po_fecha_fin_produccion,
    po_fecha_envio_programada,
    po_estado,
    po_id_cliente,
    po_id_usuario_creacion,
    po_tipo_envio,
    po_comentario,
    po_notas
) VALUES 
(
    'D-13219-M',
    '2024-09-30',
    '2024-10-05',
    NULL,
    '2025-02-25',
    'En proceso',
    1, -- ID del cliente XTB
    1, -- Usuario administrador
    'Tipo 1',
    'PO confirmada por el cliente XTB',
    'Entregar antes del 25/02/2025'
),
(
    'PO-002',
    '2023-10-02',
    '2023-10-03',
    NULL,
    '2023-10-20',
    'Pendiente',
    2, -- ID del cliente B
    1, -- Usuario administrador
    'Tipo 2',
    'PO pendiente de aprobación',
    'Urgente'
);

-- 6. Insertar Detalles de PO
INSERT INTO po_detalle (
    pd_id_po,
    pd_item,
    pd_cant_piezas_total,
    pd_pcs_carton,
    pd_pcs_poly,
    pd_estado,
    pd_precio_unitario
) VALUES 
(
    1, -- ID de la PO D-13219-M
    1, -- ID del ítem "Vestido de niña floreado"
    240,
    24,
    1,
    'Pendiente',
    2.18
),
(
    1,
    1,
    336,
    24,
    1,
    'Pendiente',
    2.49
),
(
    2, -- ID de la PO-002
    2, -- ID del ítem "Pantalón casual"
    150,
    30,
    1,
    'Pendiente',
    8.00
);

-- 7. Insertar Procesos de Producción
INSERT INTO procesos_produccion (
    pp_nombre,
    pp_descripcion,
    pp_costo
) VALUES 
('Corte', 'Proceso de corte de tela', 0.50),
('Costura', 'Proceso de confección', 1.20),
('Control de Calidad', 'Inspección final de prendas', 0.30);

-- 8. Insertar Órdenes de Producción
INSERT INTO ordenes_produccion (
    op_id_pd,
    op_operador_asignado,
    op_id_proceso,
    op_fecha_aprobacion,
    op_fecha_inicio,
    op_fecha_fin,
    op_estado,
    op_cantidad_asignada,
    op_cantidad_completada,
    op_comentario
) VALUES 
(
    1, -- ID del detalle de PO (Vestido 2T)
    2, -- Operador Juan Pérez
    1, -- ID del proceso "Corte"
    '2024-10-05',
    '2024-10-06',
    NULL,
    'En proceso',
    240,
    200, -- Completadas 200 piezas
    'Asignación inicial'
),
(
    1,
    2,
    2, -- ID del proceso "Costura"
    '2024-10-06',
    '2024-10-07',
    NULL,
    'Pendiente',
    240,
    0,
    'Esperando material'
);

-- 9. Insertar Tipos de Pruebas
INSERT INTO tipos_pruebas (
    tp_nombre,
    tp_descripcion,
    tp_requisito
) VALUES 
('Lead Testing', 'Prueba de plomo en telas y estampados', 'Menos de 30PPM'),
('CPSIA - Phthalates', 'Cumplir normas CPSIA para ftalatos', 'Aprobado por laboratorio'),
('Flammability', 'Prueba de inflamabilidad', 'CFR Title 16, Part 1610, Class 1');

-- 10. Insertar Pruebas de Calidad
INSERT INTO pruebas_calidad (
    pc_id_po,
    pc_id_tipo_prueba,
    pc_estado
) VALUES 
(1, 1, 'Pendiente'), -- Lead Testing para PO D-13219-M
(1, 2, 'Pendiente'); -- CPSIA - Phthalates para PO D-13219-M

-- 11. Insertar Resultados de Pruebas
INSERT INTO resultados_pruebas (
    rp_prueba_calidad_id,
    rp_po_detalle_id,
    rp_resultado,
    rp_observaciones,
    rp_archivo_rubrica
) VALUES 
(
    1, -- ID de la prueba Lead Testing para PO D-13219-M
    1, -- ID del detalle de PO (Vestido 2T)
    'Aprobado',
    'Cumple con el requisito de plomo',
    '/rubricas/lead_testing_25T4884MX_2T.pdf'
),
(
    1,
    2, -- ID del detalle de PO (Vestido 3T)
    'Rechazado',
    'Excede el límite de plomo',
    '/rubricas/lead_testing_25T4884MX_3T.pdf'
);

-- 12. Insertar Aprobaciones de PO
INSERT INTO aprobaciones_po (
    ap_po_id,
    ap_usuario_aprobador,
    ap_fecha_aprobacion,
    ap_estado,
    ap_comentario
) VALUES 
(
    1, -- ID de la PO D-13219-M
    1, -- Usuario administrador
    '2024-10-05',
    'Aprobada',
    'PO aprobada por el administrador'
);

-- 13. Insertar Modificaciones de PO
INSERT INTO modificaciones_po (
    mp_po_id,
    mp_usuario_modificacion,
    mp_fecha_modificacion,
    mp_campo_modificado,
    mp_valor_anterior,
    mp_valor_nuevo
) VALUES 
(
    1, -- ID de la PO D-13219-M
    1, -- Usuario administrador
    '2024-10-06 10:30:00',
    'po_estado',
    'Pendiente',
    'En proceso'
);