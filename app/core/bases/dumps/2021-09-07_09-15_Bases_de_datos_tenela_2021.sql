-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 07-09-2021 a las 09:15:13
-- Versión del servidor: 8.0.21
-- Versión de PHP: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tenela_2021_core`
--
CREATE DATABASE IF NOT EXISTS `tenela_2021_core` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `tenela_2021_core`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos_impuestos`
--

DROP TABLE IF EXISTS `cargos_impuestos`;
CREATE TABLE IF NOT EXISTS `cargos_impuestos` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `nombre` varchar(255) DEFAULT NULL COMMENT 'Nombre del cargo',
  `alias` varchar(255) DEFAULT NULL,
  `valor` float DEFAULT NULL COMMENT 'Monto del cargo (porcentaje)',
  `tipo` enum('CARGO','IMP') DEFAULT 'CARGO' COMMENT 'Tipo de cargo (cargo fijo o impuesto)',
  `calculo` enum('PORC','FIJO','TASA') DEFAULT 'PORC' COMMENT 'Tipo de cálculo',
  `aplicar` enum('CAPITALMAS','CAPITALMENOS','INTERES','CAPITALMASINTERES','TODO') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'CAPITALMAS' COMMENT 'Cómo se aplica este cargo o impuesto',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `aplica_iva` tinyint DEFAULT '0' COMMENT 'Bandera si aplica IVA sobre este cargo',
  `fechahora` datetime DEFAULT NULL COMMENT 'fecha y hora de alta',
  `usuario_id` int DEFAULT NULL COMMENT 'Usuario que da de alta',
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `cargos_impuestos`
--

INSERT INTO `cargos_impuestos` (`id`, `nombre`, `alias`, `valor`, `tipo`, `calculo`, `aplicar`, `estado`, `aplica_iva`, `fechahora`, `usuario_id`) VALUES
(1, 'IVA Inscripto', 'IVA_Insc', 21, 'IMP', 'PORC', 'INTERES', 'HAB', 0, '2019-12-02 16:10:00', 1),
(2, 'Gastos Administrativos', 'Gastos_Admin', 28.7, 'CARGO', 'PORC', 'CAPITALMAS', 'HAB', 0, '2021-04-08 08:35:59', 1),
(3, 'Ingresos Brutos', 'iibb', 10, 'IMP', 'PORC', 'INTERES', 'HAB', 0, '2021-04-08 08:36:40', 1),
(4, 'Impuesto A Las Ganancias', 'impuesto-ganancias', 30, 'IMP', 'PORC', 'INTERES', 'HAB', 0, '2019-12-06 08:21:11', 1),
(5, 'Seguro de Vida', 'seguro-vida', 2.5, 'CARGO', 'PORC', 'CAPITALMENOS', 'HAB', 1, '2019-12-06 08:54:13', 1),
(6, 'Ingreso Manual', 'ingreso-manual', 1, 'CARGO', 'PORC', 'CAPITALMAS', 'HAB', 0, '2020-03-12 15:51:00', 1),
(7, 'Cargos por cobranza', 'cargos_cobranzas', 2.5, 'CARGO', 'PORC', 'CAPITALMAS', 'HAB', 0, '2021-04-08 08:32:24', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos_planes`
--

DROP TABLE IF EXISTS `cargos_planes`;
CREATE TABLE IF NOT EXISTS `cargos_planes` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `plan_id` int DEFAULT NULL COMMENT 'ID del plan al que corresponde este cargo',
  `cargo_id` int DEFAULT NULL COMMENT 'ID del cargo de este plan',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `flag_1` tinyint DEFAULT '0' COMMENT 'Bandera para cualquier uso.',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora del alta',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora última modificación',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario de la última modificación',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Qué cargos tiene cada plan de préstamos.';

--
-- Volcado de datos para la tabla `cargos_planes`
--

INSERT INTO `cargos_planes` (`id`, `plan_id`, `cargo_id`, `estado`, `flag_1`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 1, 'HAB', 0, '2021-02-23 06:58:12', NULL, 1),
(2, 1, 2, 'HAB', 0, '2021-04-10 20:40:21', NULL, 1),
(3, 1, 7, 'HAB', 0, '2021-04-10 20:40:21', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_parametros`
--

DROP TABLE IF EXISTS `config_parametros`;
CREATE TABLE IF NOT EXISTS `config_parametros` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `grupo_id` int DEFAULT NULL COMMENT 'ID del grupo al que pertenece este parámetro',
  `nombre` varchar(64) DEFAULT NULL COMMENT 'Nombre de la variable',
  `valor` varchar(255) DEFAULT NULL COMMENT 'Valor de la variable',
  `tipo` enum('INT','FLOAT','STRING','BOOL','MONEDA') DEFAULT 'STRING' COMMENT 'Tipo de dato permitido para la variable',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `permiso` set('ADMIN','OPER') DEFAULT '' COMMENT 'Indica qué rol debe tener el usuario que puede editar este valor',
  `descripcion` text COMMENT 'Descripción del uso de la variable',
  `exponer` tinyint DEFAULT '1' COMMENT '	Indica si este parámetro está expuesto al exterior a través del WS	',
  `ofuscado` tinyint DEFAULT '0' COMMENT 'Mostrar este valor ofuscado en los listados',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de la última modificación',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de alta.',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'ID del usuario que dio el alta o modificó el registro.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `grupo_id` (`grupo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Variables de configuración del sistema';

--
-- Volcado de datos para la tabla `config_parametros`
--

INSERT INTO `config_parametros` (`id`, `grupo_id`, `nombre`, `valor`, `tipo`, `estado`, `permiso`, `descripcion`, `exponer`, `ofuscado`, `sys_fecha_modif`, `sys_fecha_alta`, `sys_usuario_id`) VALUES
(1, 1, 'body_encoding_key', '06d43abed4f73eefb6a85f3733c7661c550c49fd1422cd08b76acfcc42fc4686', 'STRING', 'HAB', '', 'Contraseña del cifrado simétrico entre el core y los clientes.', 1, 1, '2021-08-23 14:17:11', '2021-08-23 14:17:11', 1),
(2, 1, 'ws_users_secret_key', 'Extremelly secure encription key, really!', 'STRING', 'HAB', '', 'Conbraseña de cifrado de los tokens que se envían a los clientes del WS.', 1, 0, '2021-08-29 09:00:44', '2021-08-29 09:00:44', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_parametros_grupos`
--

DROP TABLE IF EXISTS `config_parametros_grupos`;
CREATE TABLE IF NOT EXISTS `config_parametros_grupos` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `nombre` varchar(255) DEFAULT NULL COMMENT 'Nombre del grupo de parámetros',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `descripcion` text COMMENT 'Descripción del grupo de parámetros',
  `fechahora_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de alta.',
  `fechahora_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación',
  `usuario_id` int DEFAULT NULL COMMENT 'Usuario que realizó la última modificación',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `config_parametros_grupos`
--

INSERT INTO `config_parametros_grupos` (`id`, `nombre`, `estado`, `descripcion`, `fechahora_alta`, `fechahora_modif`, `usuario_id`) VALUES
(1, 'Sistema local', 'HAB', 'Configuraciones del sistema local.', '2021-08-23 14:16:39', '2021-08-23 14:16:39', 1),
(2, 'Flow de otorgamiento', 'HAB', 'Configuración que afecta el flow de otorgamiento del préstamo en el frontend.', '2021-08-30 12:53:08', '2021-08-30 12:53:08', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `core_contenidos`
--

DROP TABLE IF EXISTS `core_contenidos`;
CREATE TABLE IF NOT EXISTS `core_contenidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `controlador` varchar(50) NOT NULL,
  `metadata` json NOT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `parametros` int NOT NULL DEFAULT '0',
  `en_menu` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int NOT NULL DEFAULT '999',
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `esta_protegido` tinyint(1) NOT NULL DEFAULT '0',
  `permit` tinyint(1) DEFAULT '0' COMMENT 'Indica si el contenido entra en la zona de permisos',
  `perfiles` set('ADMIN','OPER') DEFAULT NULL COMMENT 'En qué perfiles de usuario aparece este contenido',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `last_modif` datetime DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Describe de qué se trata este contenido',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Contenidos del core';

--
-- Volcado de datos para la tabla `core_contenidos`
--

INSERT INTO `core_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `descripcion`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1),
(2, 'inicio', 'Página de Inicio', 'pagina', '{\"js\": \"inicio,y,otras,cosas,bonitas\", \"css\": \"inicio\", \"vista\": \"inicio\"}', 0, 0, 1, 999, 0, 0, 0, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

DROP TABLE IF EXISTS `planes`;
CREATE TABLE IF NOT EXISTS `planes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `negocio_id` int DEFAULT NULL,
  `marca_id` int DEFAULT NULL,
  `tipo` enum('PRESTAMO','REFIN') DEFAULT 'PRESTAMO',
  `nombre_comercial` varchar(255) DEFAULT NULL,
  `alias` varchar(255) NOT NULL,
  `esdefault` tinyint DEFAULT '0' COMMENT 'Indica si el plan es el plan por omisión en la web',
  `tipo_pagos` enum('unico','diario','semanal','mensual','quicenal') DEFAULT 'diario' COMMENT 'El tipo de pagos para calcular el préstamo.',
  `devengamiento` varchar(15) DEFAULT 'frances',
  `tasa_nominal_anual` decimal(10,4) DEFAULT NULL,
  `tipo_moneda` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'ARS' COMMENT 'El tipo de moneda que se usa con este plan.',
  `score_minimo` int DEFAULT NULL,
  `score_maximo` int DEFAULT NULL,
  `vigencia_desde` date DEFAULT NULL,
  `vigencia_hasta` date DEFAULT NULL,
  `monto_minimo` decimal(13,4) DEFAULT NULL,
  `monto_maximo` decimal(13,4) DEFAULT NULL,
  `plazo_minimo` int DEFAULT NULL,
  `plazo_maximo` int DEFAULT NULL,
  `linea_credito_id` int DEFAULT NULL COMMENT 'Qué línea de crédito contable aplicar a los préstamos con este plan',
  `monto_fianza` decimal(11,4) DEFAULT NULL,
  `data` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `nombre_comercial` (`nombre_comercial`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Planes de crédito generados por el negocio';

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `negocio_id`, `marca_id`, `tipo`, `nombre_comercial`, `alias`, `esdefault`, `tipo_pagos`, `devengamiento`, `tasa_nominal_anual`, `tipo_moneda`, `score_minimo`, `score_maximo`, `vigencia_desde`, `vigencia_hasta`, `monto_minimo`, `monto_maximo`, `plazo_minimo`, `plazo_maximo`, `linea_credito_id`, `monto_fianza`, `data`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, NULL, 'PRESTAMO', 'Préstamo inicial', 'prestamo-inicial', 1, 'diario', 'frances', '150.0000', 'ARS', 100, 999, '2020-08-01', '2022-08-01', '5000.0000', '30000.0000', 7, 30, 1, NULL, '{\"step\": 500, \"TNA_Publico\": {\"valor\": 20, \"etiqueta\": \"Interés incluye IVA\"}, \"Costo_Publico\": {\"valor\": 80, \"etiqueta\": \"Costos administrativos incluye IVA\"}}', 'HAB', '2021-08-30 08:03:42', '2021-08-30 08:03:42', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ws_usuarios`
--

DROP TABLE IF EXISTS `ws_usuarios`;
CREATE TABLE IF NOT EXISTS `ws_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID del usuario. Uso interno',
  `negocio_id` int DEFAULT NULL COMMENT 'Negocio al cual pertenece este usuario del webservice',
  `username` varchar(128) DEFAULT NULL COMMENT 'Nombre de usuario de la API',
  `password` varchar(128) DEFAULT NULL COMMENT 'Contraseña asociada',
  `config` json DEFAULT NULL COMMENT 'Cualquier dato de configuración necesario',
  `modo` enum('TEST','PROD') DEFAULT NULL COMMENT 'Modo en que actúa el usuario en el sistema.',
  `bodyencodingkey` varchar(256) DEFAULT NULL COMMENT 'Esta es la clave de cifrado del cuerpo de las peticiones que este usuario usa para cifrar los mensajes que envía al servidor.',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de usuarios de la API';

--
-- Volcado de datos para la tabla `ws_usuarios`
--

INSERT INTO `ws_usuarios` (`id`, `negocio_id`, `username`, `password`, `config`, `modo`, `bodyencodingkey`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'tenelawsuser', 'mj5raft3sl2', '{}', 'TEST', 'cec23ae6bf7b02dbc2b993e24397fedd414e04a2', '2021-08-29 09:07:02', '2021-08-29 09:07:02', 1);
--
-- Base de datos: `tenela_2021_front`
--
CREATE DATABASE IF NOT EXISTS `tenela_2021_front` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `tenela_2021_front`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `frontend_contenidos`
--

DROP TABLE IF EXISTS `frontend_contenidos`;
CREATE TABLE IF NOT EXISTS `frontend_contenidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `controlador` varchar(50) NOT NULL,
  `metadata` json DEFAULT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `parametros` int NOT NULL DEFAULT '0',
  `en_menu` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int NOT NULL DEFAULT '999',
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `esta_protegido` tinyint(1) NOT NULL DEFAULT '0',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `frontend_contenidos`
--

INSERT INTO `frontend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `estado`, `sys_fecha_modif`, `sys_fecha_alta`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', NULL, 0, 0, 0, 1, 0, 0, 'HAB', '2021-08-29 17:39:33', '2021-08-29 17:39:33', NULL),
(2, 'inicio', 'Préstamos', 'pagina', '{\"js\": \"calculadora,onboarding\", \"css\": \"inicio\", \"vista\": \"inicio\", \"menutag\": \"Inicio\", \"tooltip\": \"Volver al inicio del sistema\", \"keywords\": \"Ombú, Vivus, préstamos, online, tarjeta de credito, tarjeta credito, Visa, Credial, Mastercard, Diners, Naranja, Afluenta\", \"onboarding\": true, \"calculadora\": \"visible\", \"description\": \"Something cool is coming soon\"}', 0, 0, 1, 1, 0, 0, 'HAB', '2021-08-29 17:39:33', '2021-08-29 17:39:33', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
