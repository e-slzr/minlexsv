-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-03-2025 a las 06:20:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `minlex_elsalvador`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aprobaciones_po`
--

CREATE TABLE `aprobaciones_po` (
  `id` int(11) NOT NULL,
  `ap_po_id` int(11) NOT NULL,
  `ap_usuario_aprobador` int(11) NOT NULL,
  `ap_fecha_aprobacion` date NOT NULL,
  `ap_estado` enum('Pendiente','Aprobada','Rechazada') NOT NULL,
  `ap_comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `aprobaciones_po`
--

INSERT INTO `aprobaciones_po` (`id`, `ap_po_id`, `ap_usuario_aprobador`, `ap_fecha_aprobacion`, `ap_estado`, `ap_comentario`) VALUES
(1, 1, 1, '2025-03-15', 'Aprobada', 'PO aprobada por el administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `cliente_empresa` varchar(50) NOT NULL,
  `cliente_nombre` varchar(50) NOT NULL,
  `cliente_apellido` varchar(50) NOT NULL,
  `cliente_direccion` text DEFAULT NULL,
  `cliente_telefono` varchar(20) DEFAULT NULL,
  `cliente_correo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `cliente_empresa`, `cliente_nombre`, `cliente_apellido`, `cliente_direccion`, `cliente_telefono`, `cliente_correo`) VALUES
(1, 'XTB', 'Carlos', 'López', 'NY, Piscataway, NJ', '555-1234', 'carlos.lopez@xtb.com'),
(2, 'Cliente B', 'Laura', 'Martínez', 'Ciudad Arce, El Salvador', '555-5678', 'laura.martinez@cliente-b.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `item_numero` varchar(50) NOT NULL,
  `item_nombre` varchar(100) NOT NULL,
  `item_descripcion` text DEFAULT NULL,
  `item_talla` varchar(10) DEFAULT NULL,
  `item_img` text DEFAULT NULL,
  `item_dir_specs` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `items`
--

INSERT INTO `items` (`id`, `item_numero`, `item_nombre`, `item_descripcion`, `item_talla`, `item_img`, `item_dir_specs`) VALUES
(1, '25T4884MX', 'Vestido de niña floreado', 'Vestido infantil estampado', '2T', '/imagenes/25T4884MX.jpg', '/specs/25T4884MX.pdf'),
(2, '35T4884MX', 'Pantalón casual', 'Pantalón de tela ligera', 'L', '/imagenes/35T4884MX.jpg', '/specs/35T4884MX.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modificaciones_po`
--

CREATE TABLE `modificaciones_po` (
  `id` int(11) NOT NULL,
  `mp_po_id` int(11) NOT NULL,
  `mp_usuario_modificacion` int(11) NOT NULL,
  `mp_fecha_modificacion` datetime NOT NULL,
  `mp_campo_modificado` varchar(100) NOT NULL,
  `mp_valor_anterior` text DEFAULT NULL,
  `mp_valor_nuevo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `modificaciones_po`
--

INSERT INTO `modificaciones_po` (`id`, `mp_po_id`, `mp_usuario_modificacion`, `mp_fecha_modificacion`, `mp_campo_modificado`, `mp_valor_anterior`, `mp_valor_nuevo`) VALUES
(1, 1, 1, '2025-03-15 00:00:00', 'po_estado', 'Pendiente', 'En proceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_produccion`
--

CREATE TABLE `ordenes_produccion` (
  `id` int(11) NOT NULL,
  `op_id_pd` int(11) NOT NULL,
  `op_operador_asignado` int(11) NOT NULL,
  `op_id_proceso` int(11) NOT NULL,
  `op_fecha_aprobacion` date DEFAULT NULL,
  `op_fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `op_fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `op_fecha_inicio` date DEFAULT NULL,
  `op_fecha_fin` date DEFAULT NULL,
  `op_estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `op_cantidad_asignada` int(11) NOT NULL,
  `op_cantidad_completada` int(11) DEFAULT 0,
  `op_comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `ordenes_produccion`
--

INSERT INTO `ordenes_produccion` (`id`, `op_id_pd`, `op_operador_asignado`, `op_id_proceso`, `op_fecha_aprobacion`, `op_fecha_creacion`, `op_fecha_modificacion`, `op_fecha_inicio`, `op_fecha_fin`, `op_estado`, `op_cantidad_asignada`, `op_cantidad_completada`, `op_comentario`) VALUES
(1, 1, 1, 1, '2025-03-15', '2025-03-15 07:50:29', '2025-03-15 07:50:29', '2025-03-16', NULL, 'En proceso', 240, 200, 'Asignación inicial'),
(2, 1, 1, 2, '2025-03-15', '2025-03-15 07:50:29', '2025-03-15 07:50:29', '2025-03-17', NULL, 'Pendiente', 240, 0, 'Esperando material');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `po`
--

CREATE TABLE `po` (
  `id` int(11) NOT NULL,
  `po_numero` varchar(50) NOT NULL,
  `po_fecha_creacion` date NOT NULL,
  `po_fecha_inicio_produccion` date DEFAULT NULL,
  `po_fecha_fin_produccion` date DEFAULT NULL,
  `po_fecha_envio_programada` date DEFAULT NULL,
  `po_estado` enum('Pendiente','En proceso','Completada','Cancelada') DEFAULT 'Pendiente',
  `po_id_cliente` int(11) NOT NULL,
  `po_id_usuario_creacion` int(11) NOT NULL,
  `po_tipo_envio` enum('Tipo 1','Tipo 2','Tipo 3') DEFAULT 'Tipo 1',
  `po_comentario` text DEFAULT NULL,
  `po_notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `po`
--

INSERT INTO `po` (`id`, `po_numero`, `po_fecha_creacion`, `po_fecha_inicio_produccion`, `po_fecha_fin_produccion`, `po_fecha_envio_programada`, `po_estado`, `po_id_cliente`, `po_id_usuario_creacion`, `po_tipo_envio`, `po_comentario`, `po_notas`) VALUES
(1, 'D-13219-M', '2025-03-15', '2025-03-20', NULL, '2025-04-14', 'En proceso', 1, 1, 'Tipo 1', 'PO confirmada por el cliente XTB', 'Entregar antes del 25/02/2025'),
(2, 'PO-002', '2025-03-15', '2025-03-16', NULL, '2025-03-30', 'Pendiente', 2, 1, 'Tipo 2', 'PO pendiente de aprobación', 'Urgente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `po_detalle`
--

CREATE TABLE `po_detalle` (
  `id` int(11) NOT NULL,
  `pd_id_po` int(11) NOT NULL,
  `pd_item` int(11) NOT NULL,
  `pd_cant_piezas_total` int(11) NOT NULL,
  `pd_pcs_carton` int(11) NOT NULL,
  `pd_pcs_poly` int(11) NOT NULL,
  `pd_estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `pd_precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `po_detalle`
--

INSERT INTO `po_detalle` (`id`, `pd_id_po`, `pd_item`, `pd_cant_piezas_total`, `pd_pcs_carton`, `pd_pcs_poly`, `pd_estado`, `pd_precio_unitario`) VALUES
(1, 1, 1, 240, 24, 1, 'Pendiente', 2.18),
(2, 1, 1, 336, 24, 1, 'Pendiente', 2.49),
(3, 2, 2, 150, 30, 1, 'Pendiente', 8.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `po_detalle_cantidades`
--

CREATE TABLE `po_detalle_cantidades` (
  `id` int(11) NOT NULL,
  `pdc_id_po_detalle` int(11) NOT NULL,
  `pdc_cant_piezas` int(11) NOT NULL,
  `pdc_estado` enum('Pendiente','En proceso','Completado') DEFAULT 'Pendiente',
  `pdc_precio_unitario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `procesos_produccion`
--

CREATE TABLE `procesos_produccion` (
  `id` int(11) NOT NULL,
  `pp_nombre` varchar(100) NOT NULL,
  `pp_descripcion` text DEFAULT NULL,
  `pp_costo` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `procesos_produccion`
--

INSERT INTO `procesos_produccion` (`id`, `pp_nombre`, `pp_descripcion`, `pp_costo`) VALUES
(1, 'Corte', 'Proceso de corte de tela', 0.50),
(2, 'Costura', 'Proceso de confección', 1.20),
(3, 'Control de Calidad', 'Inspección final de prendas', 0.30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pruebas_calidad`
--

CREATE TABLE `pruebas_calidad` (
  `id` int(11) NOT NULL,
  `pc_id_po` int(11) NOT NULL,
  `pc_id_tipo_prueba` int(11) NOT NULL,
  `pc_estado` enum('Pendiente','Completado','Rechazado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `pruebas_calidad`
--

INSERT INTO `pruebas_calidad` (`id`, `pc_id_po`, `pc_id_tipo_prueba`, `pc_estado`) VALUES
(1, 1, 1, 'Pendiente'),
(2, 1, 2, 'Pendiente'),
(3, 1, 3, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultados_pruebas`
--

CREATE TABLE `resultados_pruebas` (
  `id` int(11) NOT NULL,
  `rp_prueba_calidad_id` int(11) NOT NULL,
  `rp_po_detalle_id` int(11) NOT NULL,
  `rp_resultado` enum('Aprobado','Rechazado') NOT NULL,
  `rp_observaciones` varchar(255) DEFAULT NULL,
  `rp_archivo_rubrica` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `resultados_pruebas`
--

INSERT INTO `resultados_pruebas` (`id`, `rp_prueba_calidad_id`, `rp_po_detalle_id`, `rp_resultado`, `rp_observaciones`, `rp_archivo_rubrica`) VALUES
(1, 1, 1, 'Aprobado', 'Cumple con los requisitos', '/rubricas/prueba_1.pdf'),
(2, 2, 1, 'Aprobado', 'Color dentro de parámetros', '/rubricas/prueba_2.pdf'),
(3, 3, 1, 'Aprobado', 'Medidas correctas', '/rubricas/prueba_3.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `rol_nombre` varchar(25) NOT NULL,
  `rol_descripcion` text DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `rol_nombre`, `rol_descripcion`, `estado`) VALUES
(1, 'Administrador', 'Usuario con acceso total al sistema.', 'Activo'),
(2, 'Operador', 'Usuario responsable de procesos de producción.', 'Activo'),
(3, 'Calidad', 'Usuario encargado de pruebas y aprobaciones.', 'Inactivo'),
(4, 'Auditor corte', 'Auditor de calidad proceso de corte.', 'Inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_pruebas`
--

CREATE TABLE `tipos_pruebas` (
  `id` int(11) NOT NULL,
  `tp_nombre` varchar(100) NOT NULL,
  `tp_descripcion` text DEFAULT NULL,
  `tp_requisito` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tipos_pruebas`
--

INSERT INTO `tipos_pruebas` (`id`, `tp_nombre`, `tp_descripcion`, `tp_requisito`) VALUES
(1, 'Prueba de Costura', 'Verificación de resistencia de costuras', 'Resistencia mínima 10N'),
(2, 'Prueba de Color', 'Verificación de solidez del color', 'No desteñir'),
(3, 'Prueba de Medidas', 'Verificación de dimensiones', 'Tolerancia ±0.5cm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario_alias` varchar(25) NOT NULL,
  `usuario_nombre` varchar(25) NOT NULL,
  `usuario_apellido` varchar(25) NOT NULL,
  `usuario_password` varchar(255) NOT NULL,
  `usuario_rol_id` int(11) NOT NULL,
  `usuario_departamento` varchar(25) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  `usuario_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario_alias`, `usuario_nombre`, `usuario_apellido`, `usuario_password`, `usuario_rol_id`, `usuario_departamento`, `estado`, `usuario_creacion`, `usuario_modificacion`) VALUES
(1, 'admin', 'Eliu', 'Salazar', '$2y$10$DK4EGOXPHfLbpYqO9zaoH.uDWJx/HCaPOzsa4U8x39ViQBQ/VMbnG', 1, 'Corte', 'Activo', '2025-03-15 07:50:29', '2025-03-15 18:20:44'),
(3, 'fabian', 'Edenilson', 'Salas', '$2y$10$4S4EeALR3H7cS7ZYyZOJl.G3klh7bd1dqlSEI0g/FTxjCxveDqBuK', 2, 'Corte', 'Activo', '2025-03-15 12:13:42', '2025-03-15 18:10:34'),
(4, 'paola.mezquita', 'Paola', 'Mezquita', '$2y$10$bVstBaofeCg2LGVxWAHal.9lnQp6tUE0dthZ0r0qn8YKwSQfFpc4u', 2, 'Corte', 'Inactivo', '2025-03-15 16:41:02', '2025-03-15 18:39:06');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aprobaciones_po`
--
ALTER TABLE `aprobaciones_po`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ap_po_id` (`ap_po_id`),
  ADD KEY `ap_usuario_aprobador` (`ap_usuario_aprobador`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_numero` (`item_numero`);

--
-- Indices de la tabla `modificaciones_po`
--
ALTER TABLE `modificaciones_po`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mp_po_id` (`mp_po_id`),
  ADD KEY `mp_usuario_modificacion` (`mp_usuario_modificacion`);

--
-- Indices de la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `op_id_pd` (`op_id_pd`),
  ADD KEY `op_id_proceso` (`op_id_proceso`),
  ADD KEY `op_operador_asignado` (`op_operador_asignado`);

--
-- Indices de la tabla `po`
--
ALTER TABLE `po`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_numero` (`po_numero`),
  ADD KEY `po_id_cliente` (`po_id_cliente`),
  ADD KEY `po_id_usuario_creacion` (`po_id_usuario_creacion`),
  ADD KEY `idx_po_numero` (`po_numero`);

--
-- Indices de la tabla `po_detalle`
--
ALTER TABLE `po_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pd_id_po` (`pd_id_po`),
  ADD KEY `pd_item` (`pd_item`);

--
-- Indices de la tabla `po_detalle_cantidades`
--
ALTER TABLE `po_detalle_cantidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pdc_id_po_detalle` (`pdc_id_po_detalle`);

--
-- Indices de la tabla `procesos_produccion`
--
ALTER TABLE `procesos_produccion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pruebas_calidad`
--
ALTER TABLE `pruebas_calidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pc_id_po` (`pc_id_po`),
  ADD KEY `pc_id_tipo_prueba` (`pc_id_tipo_prueba`);

--
-- Indices de la tabla `resultados_pruebas`
--
ALTER TABLE `resultados_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rp_prueba_calidad_id` (`rp_prueba_calidad_id`),
  ADD KEY `rp_po_detalle_id` (`rp_po_detalle_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rol_nombre` (`rol_nombre`);

--
-- Indices de la tabla `tipos_pruebas`
--
ALTER TABLE `tipos_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tp_nombre` (`tp_nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_alias` (`usuario_alias`),
  ADD KEY `usuario_rol_id` (`usuario_rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aprobaciones_po`
--
ALTER TABLE `aprobaciones_po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `modificaciones_po`
--
ALTER TABLE `modificaciones_po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `po`
--
ALTER TABLE `po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `po_detalle`
--
ALTER TABLE `po_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `po_detalle_cantidades`
--
ALTER TABLE `po_detalle_cantidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `procesos_produccion`
--
ALTER TABLE `procesos_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pruebas_calidad`
--
ALTER TABLE `pruebas_calidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `resultados_pruebas`
--
ALTER TABLE `resultados_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipos_pruebas`
--
ALTER TABLE `tipos_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aprobaciones_po`
--
ALTER TABLE `aprobaciones_po`
  ADD CONSTRAINT `aprobaciones_po_ibfk_1` FOREIGN KEY (`ap_po_id`) REFERENCES `po` (`id`),
  ADD CONSTRAINT `aprobaciones_po_ibfk_2` FOREIGN KEY (`ap_usuario_aprobador`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `modificaciones_po`
--
ALTER TABLE `modificaciones_po`
  ADD CONSTRAINT `modificaciones_po_ibfk_1` FOREIGN KEY (`mp_po_id`) REFERENCES `po` (`id`),
  ADD CONSTRAINT `modificaciones_po_ibfk_2` FOREIGN KEY (`mp_usuario_modificacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  ADD CONSTRAINT `ordenes_produccion_ibfk_1` FOREIGN KEY (`op_id_pd`) REFERENCES `po_detalle` (`id`),
  ADD CONSTRAINT `ordenes_produccion_ibfk_2` FOREIGN KEY (`op_id_proceso`) REFERENCES `procesos_produccion` (`id`),
  ADD CONSTRAINT `ordenes_produccion_ibfk_3` FOREIGN KEY (`op_operador_asignado`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `po`
--
ALTER TABLE `po`
  ADD CONSTRAINT `po_ibfk_1` FOREIGN KEY (`po_id_cliente`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `po_ibfk_2` FOREIGN KEY (`po_id_usuario_creacion`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `po_detalle`
--
ALTER TABLE `po_detalle`
  ADD CONSTRAINT `po_detalle_ibfk_1` FOREIGN KEY (`pd_id_po`) REFERENCES `po` (`id`),
  ADD CONSTRAINT `po_detalle_ibfk_2` FOREIGN KEY (`pd_item`) REFERENCES `items` (`id`);

--
-- Filtros para la tabla `po_detalle_cantidades`
--
ALTER TABLE `po_detalle_cantidades`
  ADD CONSTRAINT `po_detalle_cantidades_ibfk_1` FOREIGN KEY (`pdc_id_po_detalle`) REFERENCES `po_detalle` (`id`);

--
-- Filtros para la tabla `pruebas_calidad`
--
ALTER TABLE `pruebas_calidad`
  ADD CONSTRAINT `pruebas_calidad_ibfk_1` FOREIGN KEY (`pc_id_po`) REFERENCES `po` (`id`),
  ADD CONSTRAINT `pruebas_calidad_ibfk_2` FOREIGN KEY (`pc_id_tipo_prueba`) REFERENCES `tipos_pruebas` (`id`);

--
-- Filtros para la tabla `resultados_pruebas`
--
ALTER TABLE `resultados_pruebas`
  ADD CONSTRAINT `resultados_pruebas_ibfk_1` FOREIGN KEY (`rp_prueba_calidad_id`) REFERENCES `pruebas_calidad` (`id`),
  ADD CONSTRAINT `resultados_pruebas_ibfk_2` FOREIGN KEY (`rp_po_detalle_id`) REFERENCES `po_detalle` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`usuario_rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
