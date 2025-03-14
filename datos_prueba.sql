USE minlex_elsalvador;

-- 1. Insertar Roles
INSERT INTO roles (rol_nombre, rol_descripcion) VALUES 
('Administrador', 'Usuario con acceso total al sistema'),
('Operador', 'Usuario responsable de procesos de producción'),
('Calidad', 'Usuario encargado de pruebas y aprobaciones');

-- 2. Insertar Usuarios (con contraseña hasheada)
INSERT INTO usuarios (
    usuario_alias,
    usuario_nombre,
    usuario_apellido,
    usuario_password,
    usuario_rol_id,
    usuario_departamento
) VALUES 
(
    'admin',
    'Administrador',
    'Sistema',
    'admin',
    1,
    'Administración'
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
    CURRENT_DATE(),
    DATE_ADD(CURRENT_DATE(), INTERVAL 5 DAY),
    NULL,
    DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY),
    'En proceso',
    1,
    1,
    'Tipo 1',
    'PO confirmada por el cliente XTB',
    'Entregar antes del 25/02/2025'
),
(
    'PO-002',
    CURRENT_DATE(),
    DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY),
    NULL,
    DATE_ADD(CURRENT_DATE(), INTERVAL 15 DAY),
    'Pendiente',
    2,
    1,
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
    1,
    1,
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
    2,
    2,
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
    1,
    1,
    1,
    CURRENT_DATE(),
    DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY),
    NULL,
    'En proceso',
    240,
    200,
    'Asignación inicial'
),
(
    1,
    1,
    2,
    CURRENT_DATE(),
    DATE_ADD(CURRENT_DATE(), INTERVAL 2 DAY),
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
('Prueba de Costura', 'Verificación de resistencia de costuras', 'Resistencia mínima 10N'),
('Prueba de Color', 'Verificación de solidez del color', 'No desteñir'),
('Prueba de Medidas', 'Verificación de dimensiones', 'Tolerancia ±0.5cm');

-- 10. Insertar Pruebas de Calidad
INSERT INTO pruebas_calidad (
    pc_id_po,
    pc_id_tipo_prueba,
    pc_estado
) VALUES 
(1, 1, 'Pendiente'),
(1, 2, 'Pendiente'),
(1, 3, 'Pendiente');

-- 11. Insertar Resultados de Pruebas
INSERT INTO resultados_pruebas (
    rp_prueba_calidad_id,
    rp_po_detalle_id,
    rp_resultado,
    rp_observaciones,
    rp_archivo_rubrica
) VALUES 
(1, 1, 'Aprobado', 'Cumple con los requisitos', '/rubricas/prueba_1.pdf'),
(2, 1, 'Aprobado', 'Color dentro de parámetros', '/rubricas/prueba_2.pdf'),
(3, 1, 'Aprobado', 'Medidas correctas', '/rubricas/prueba_3.pdf');

-- 12. Insertar Aprobaciones de PO
INSERT INTO aprobaciones_po (
    ap_po_id,
    ap_usuario_aprobador,
    ap_fecha_aprobacion,
    ap_estado,
    ap_comentario
) VALUES 
(
    1,
    1,
    CURRENT_DATE(),
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
    1,
    1,
    CURRENT_DATE(),
    'po_estado',
    'Pendiente',
    'En proceso'
);