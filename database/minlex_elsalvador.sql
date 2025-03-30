-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-03-2025 a las 09:46:05
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
  `cliente_correo` varchar(100) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `cliente_empresa`, `cliente_nombre`, `cliente_apellido`, `cliente_direccion`, `cliente_telefono`, `cliente_correo`, `estado`) VALUES
(1, 'XTB', 'Carlos', 'López', 'NY, Piscataway, NJ', '555-1234', 'carlos.lopez@xtb.com', 'Activo'),
(2, 'Adidas Inc.', 'Laura', 'Martínez', 'Ciudad Arce, El Salvador', '555-5678', 'laura.martinez@cliente-b.com', 'Activo'),
(3, 'Empresa Alfa', 'Juan', 'Perez', 'Calle 123, San Salvador', '5555-5555', 'juan.perez@alfa.com', 'Activo'),
(4, 'Empresa Beta', 'Maria', 'Gomez', 'Avenida 45, Santa Tecla', '7777-7777', 'maria.gomez@beta.com', 'Inactivo'),
(5, 'Empresa Gamma', 'Carlos', 'Lopez', 'Boulevard Norte, Soyapango', '6666-6666', 'carlos.lopez@gamma.com', 'Activo'),
(6, 'Empresa Delta', 'Ana', 'Martinez', 'Colonia Centro, La Libertad', '8888-8888', 'ana.martinez@delta.com', 'Inactivo'),
(7, 'Empresa Epsilon', 'Pedro', 'Ramirez', 'Residencial Las Rosas, Usulután', '9999-9999', 'pedro.ramirez@epsilon.com', 'Inactivo'),
(8, 'Empresa Zeta', 'Luisa', 'Fernandez', 'Zona Industrial, San Miguel', '5556-5556', 'luisa.fernandez@zeta.com', 'Inactivo'),
(9, 'Empresa Eta', 'Ricardo', 'Mendez', 'Calle Los Próceres, San Salvador', '7778-7778', 'ricardo.mendez@eta.com', 'Inactivo'),
(10, 'Empresa Theta', 'Laura', 'Castillo', 'Av. Los Olivos, Santa Ana', '6667-6667', 'laura.castillo@theta.com', 'Activo'),
(11, 'Empresa Iota', 'Jorge', 'Hernandez', 'Col. Escalón, San Salvador', '8889-8889', 'jorge.hernandez@iota.com', 'Activo'),
(12, 'Empresa Kappa', 'Elena Elvira', 'Vasquez', 'Residencial Altavista, Ilopango', '9990-9990', 'elena.vasquez@kappa.com', 'Inactivo'),
(13, 'Nike', 'Nike', 'US', 'Nike Inc.', '', '', 'Inactivo'),
(14, 'ECODE', 'Adilson', 'Salazar', 'Santa Ana, El Salvador', '2543-5685', '', 'Inactivo');

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
(3, 'T-0001-MH', 'Camiseta simple M Hombre', 'Camiseta simple M Hombre', 'M', '/uploads/imagenes/shirt01.jpg', '/uploads/specs/1743134976_T_0001_MH/'),
(4, 'T-0002-SH', 'Camiseta Celeste simple S Hombre', 'Camiseta Celeste simple S Hombre', 'S', '/uploads/imagenes/4049047-800-800.jpg', '/uploads/specs/1743139696_T_0002_SH/'),
(8, 'C-0003-XLH', 'Sueter para hombre estilo Nike deportiva', 'Sueter para hombre estilo Nike deportiva', 'XL', '/uploads/imagenes/img00.png', '/uploads/specs/1743307004_C_0003_XLH/');

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
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `modulo_codigo` varchar(10) NOT NULL COMMENT 'Código del módulo (ej: C-01, T-01)',
  `modulo_tipo` enum('Costura','Corte') NOT NULL COMMENT 'Tipo de módulo',
  `modulo_descripcion` text DEFAULT NULL COMMENT 'Descripción o notas sobre el módulo',
  `modulo_estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `modulo_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `modulo_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modulos`
--

INSERT INTO `modulos` (`id`, `modulo_codigo`, `modulo_tipo`, `modulo_descripcion`, `modulo_estado`, `modulo_creacion`, `modulo_modificacion`) VALUES
(1, 'C-01', 'Costura', 'Modulo 1 de costura', 'Activo', '2025-03-23 07:40:23', '2025-03-23 07:40:23'),
(2, 'T-01', 'Corte', 'Mesa 1 de corte', 'Activo', '2025-03-30 02:47:30', '2025-03-30 03:04:45'),
(3, 'C-02', 'Costura', 'Modulo 2 de costura', 'Inactivo', '2025-03-30 03:05:31', '2025-03-30 03:06:50');

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
  `op_comentario` text DEFAULT NULL,
  `op_modulo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `po_notas` text DEFAULT NULL,
  `po_fecha_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `po`
--

INSERT INTO `po` (`id`, `po_numero`, `po_fecha_creacion`, `po_fecha_inicio_produccion`, `po_fecha_fin_produccion`, `po_fecha_envio_programada`, `po_estado`, `po_id_cliente`, `po_id_usuario_creacion`, `po_tipo_envio`, `po_comentario`, `po_notas`, `po_fecha_modificacion`) VALUES
(1, 'D-13219-M', '2025-03-15', '2025-03-20', NULL, '2025-04-14', 'En proceso', 1, 1, 'Tipo 1', 'PO confirmada por el cliente XTB', 'Entregar antes del 25/02/2025', '2025-03-30 07:16:17'),
(80, 'PO-000001', '2025-03-29', NULL, NULL, '2025-04-30', 'Pendiente', 2, 1, 'Tipo 1', 'Comentario de prueba para la PO-000001', NULL, '2025-03-30 07:16:17'),
(84, 'PO-000002', '2025-03-30', NULL, NULL, '2025-06-30', 'Pendiente', 3, 1, 'Tipo 1', 'Texto de prueba.', 'In hac habitasse platea dictumst. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam eu nunc non augue tincidunt suscipit. Suspendisse potenti. Aliquam erat volutpat. Integer vel turpis sed purus scelerisque euismod.\r\n\r\nPhasellus ac eros at urna condimentum lacinia. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed bibendum, sapien a venenatis fermentum, mauris augue cursus turpis, vitae elementum massa orci sit amet massa. In hac habitasse platea dictumst.\r\n\r\nFusce at nisi arcu. Quisque sed dolor nec dui scelerisque dapibus. Sed at purus at sem aliquet luctus. Sed non massa sit amet sapien porttitor ornare. Vivamus pretium, tortor at tempus ullamcorper, diam ligula lobortis quam, at scelerisque libero lectus ut risus.\r\n\r\nVivamus lacinia lacus vel neque egestas, vitae volutpat purus dapibus. Nullam nec ultricies erat. Etiam ac urna metus. Sed cursus libero id ullamcorper interdum. Donec non urna et erat vehicula porttitor. Vivamus a sagittis dolor. Nulla facilisi. Cras euismod orci at felis cursus, vel vulputate sapien suscipit.\r\n\r\nCurabitur at felis non libero suscipit fermentum. Duis volutpat, ante et scelerisque luctus, sem nulla placerat leo, at aliquet libero justo id nulla. Integer at dui nec magna posuere fringilla. Nunc euismod bibendum augue. Cras nec ligula velit. Donec in laoreet leo.', '2025-03-30 07:16:17'),
(85, 'PO-000003', '2025-03-30', NULL, NULL, '2025-04-25', 'Pendiente', 5, 1, 'Tipo 2', 'N/A', 'In hac habitasse platea dictumst. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam eu nunc non augue tincidunt suscipit. Suspendisse potenti. Aliquam erat volutpat. Integer vel turpis sed purus scelerisque euismod.\r\n\r\nPhasellus ac eros at urna condimentum lacinia. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed bibendum, sapien a venenatis fermentum, mauris augue cursus turpis, vitae elementum massa orci sit amet massa. In hac habitasse platea dictumst.\r\n\r\nFusce at nisi arcu. Quisque sed dolor nec dui scelerisque dapibus. Sed at purus at sem aliquet luctus. Sed non massa sit amet sapien porttitor ornare. Vivamus pretium, tortor at tempus ullamcorper, diam ligula lobortis quam, at scelerisque libero lectus ut risus.\r\n\r\nVivamus lacinia lacus vel neque egestas, vitae volutpat purus dapibus. Nullam nec ultricies erat. Etiam ac urna metus. Sed cursus libero id ullamcorper interdum. Donec non urna et erat vehicula porttitor. Vivamus a sagittis dolor. Nulla facilisi. Cras euismod orci at felis cursus, vel vulputate sapien suscipit.\r\n\r\nCurabitur at felis non libero suscipit fermentum. Duis volutpat, ante et scelerisque luctus, sem nulla placerat leo, at aliquet libero justo id nulla. Integer at dui nec magna posuere fringilla. Nunc euismod bibendum augue. Cras nec ligula velit. Donec in laoreet leo.', '2025-03-30 07:16:17');

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
(4, 80, 4, 1000, 12, 1, 'Pendiente', 1.75),
(5, 80, 3, 1000, 12, 1, 'Pendiente', 1.50),
(6, 84, 8, 5000, 12, 1, 'Pendiente', 2.50),
(7, 85, 3, 1500, 12, 1, 'Pendiente', 1.25);

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
(3, 'Calidad', 'Usuario encargado de pruebas y aprobaciones.', 'Activo'),
(4, 'Auditor corte', 'Auditor de calidad proceso de corte.', 'Activo'),
(5, 'Operador de costura', 'Operador de costura.', 'Activo'),
(6, 'Rol 01', 'Rol de test 01', 'Activo'),
(9, 'Rol 02', 'Rol de test 02', 'Activo'),
(10, 'Rol 03', 'Rol de test 03', 'Inactivo'),
(11, 'Rol 04', 'Rol de test 04', 'Activo');

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
  `usuario_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modulo_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario_alias`, `usuario_nombre`, `usuario_apellido`, `usuario_password`, `usuario_rol_id`, `usuario_departamento`, `estado`, `usuario_creacion`, `usuario_modificacion`, `usuario_modulo_id`) VALUES
(1, 'admin', 'Eliu', 'Salazar', '$2y$10$DK4EGOXPHfLbpYqO9zaoH.uDWJx/HCaPOzsa4U8x39ViQBQ/VMbnG', 1, 'IT', 'Activo', '2025-03-15 07:50:29', '2025-03-30 02:53:45', 1),
(3, 'fabian', 'Edenilson', 'Salas', '$2y$10$4S4EeALR3H7cS7ZYyZOJl.G3klh7bd1dqlSEI0g/FTxjCxveDqBuK', 2, 'Corte', 'Inactivo', '2025-03-15 12:13:42', '2025-03-30 00:37:29', NULL),
(4, 'paola.mezquita', 'Paola', 'Mezquita', '$2y$10$zBTTTBzfvfomvJsYFepEsOnjXqIVgYG0zYwtPHf.3qXaTHwmS5G36', 2, 'Corte', 'Activo', '2025-03-15 16:41:02', '2025-03-30 00:34:01', NULL),
(5, 'admin1', 'Carlos', 'Gonzalez', 'hashedpassword1', 1, 'IT', 'Activo', '2025-03-18 06:38:39', '2025-03-30 03:58:30', NULL),
(6, 'admin2', 'Laura', 'Rodriguez', 'hashedpassword2', 2, 'Recursos Humanos', 'Inactivo', '2025-03-18 06:38:39', '2025-03-30 00:19:59', NULL),
(7, 'user1', 'Jose', 'Martinez', 'hashedpassword3', 3, 'Ventas', 'Inactivo', '2025-03-18 06:38:39', '2025-03-23 08:24:05', NULL),
(8, 'user2', 'Ana', 'Lopez', 'hashedpassword4', 4, 'Soporte', 'Inactivo', '2025-03-18 06:38:39', '2025-03-23 08:23:59', NULL),
(9, 'user3', 'Miguel', 'Hernandez', 'hashedpassword5', 5, 'Logística', 'Inactivo', '2025-03-18 06:38:39', '2025-03-30 00:19:05', NULL),
(10, 'test', 'usuario', 'test', '$2y$10$0uO5Z5YGZemnXKbHF4hsK.95CqwLslPyxmhZlD6ukYo906Q78M6lC', 1, 'Corte', 'Inactivo', '2025-03-21 05:11:02', '2025-03-30 00:21:36', NULL),
(11, 'jessica.salazar', 'Jessica', 'Salazar', '$2y$10$IzOhfgRBGmEnS1GBocwV6.J.EkbjcSdH6nvWkqUUiu0Lk2dSH47We', 2, 'Produccion', 'Inactivo', '2025-03-21 05:12:10', '2025-03-30 03:10:39', 2),
(12, 'fede.valverde', 'Federico', 'Valverde', '$2y$10$ltH3o7epBz1.I3I58sdQUeq5E2jVaHplCTcRBgX6IpeiwG0g88FAe', 1, 'Calidad', 'Inactivo', '2025-03-30 00:13:09', '2025-03-30 02:46:59', 1),
(13, 'allison.cartagena', 'Allison', 'Cartagena', '$2y$10$Q6K99rOfu9q8uQfk9ek5IOVhmvW4nZtuLjQRFvtvG.0000ED8f44O', 2, 'Compras', 'Inactivo', '2025-03-30 00:23:05', '2025-03-30 00:39:59', 1);

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
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_modulo_codigo` (`modulo_codigo`);

--
-- Indices de la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `op_id_pd` (`op_id_pd`),
  ADD KEY `op_id_proceso` (`op_id_proceso`),
  ADD KEY `op_operador_asignado` (`op_operador_asignado`),
  ADD KEY `op_modulo_id` (`op_modulo_id`);

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
  ADD KEY `usuario_rol_id` (`usuario_rol_id`),
  ADD KEY `fk_usuario_modulo` (`usuario_modulo_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `modificaciones_po`
--
ALTER TABLE `modificaciones_po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `po`
--
ALTER TABLE `po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `po_detalle`
--
ALTER TABLE `po_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tipos_pruebas`
--
ALTER TABLE `tipos_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  ADD CONSTRAINT `ordenes_produccion_ibfk_3` FOREIGN KEY (`op_operador_asignado`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `ordenes_produccion_ibfk_4` FOREIGN KEY (`op_modulo_id`) REFERENCES `modulos` (`id`);

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
  ADD CONSTRAINT `fk_usuario_modulo` FOREIGN KEY (`usuario_modulo_id`) REFERENCES `modulos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`usuario_rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
