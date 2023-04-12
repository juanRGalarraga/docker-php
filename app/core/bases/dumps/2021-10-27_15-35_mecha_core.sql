-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 27-10-2021 a las 15:35:10
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
-- Base de datos: `mecha_core`
--
CREATE DATABASE IF NOT EXISTS `mecha_core` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mecha_core`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ban_list`
--

DROP TABLE IF EXISTS `ban_list`;
CREATE TABLE IF NOT EXISTS `ban_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(10) DEFAULT NULL,
  `valor` varchar(255) DEFAULT NULL,
  `expira` datetime DEFAULT NULL COMMENT 'Momento a partir del cual la exclusión deja de tener efecto',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`),
  KEY `expira` (`expira`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='Datos que determinan la exclusión del sistema';

--
-- Truncar tablas antes de insertar `ban_list`
--

TRUNCATE TABLE `ban_list`;
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bind_accounts`
--

DROP TABLE IF EXISTS `bind_accounts`;
CREATE TABLE IF NOT EXISTS `bind_accounts` (
  `id` int NOT NULL,
  `negocio_id` int DEFAULT NULL,
  `account_id` varchar(15) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `es_default` smallint DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `bind_accounts`
--

TRUNCATE TABLE `bind_accounts`;
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
-- Truncar tablas antes de insertar `cargos_impuestos`
--

TRUNCATE TABLE `cargos_impuestos`;
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
-- Truncar tablas antes de insertar `cargos_planes`
--

TRUNCATE TABLE `cargos_planes`;
--
-- Volcado de datos para la tabla `cargos_planes`
--

INSERT INTO `cargos_planes` (`id`, `plan_id`, `cargo_id`, `estado`, `flag_1`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 1, 'HAB', 0, '2021-02-23 06:58:12', NULL, 1),
(2, 1, 2, 'HAB', 0, '2021-04-10 20:40:21', NULL, 1),
(3, 1, 7, 'HAB', 0, '2021-04-10 20:40:21', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_bancos`
--

DROP TABLE IF EXISTS `config_bancos`;
CREATE TABLE IF NOT EXISTS `config_bancos` (
  `nro` int NOT NULL AUTO_INCREMENT,
  `id` int NOT NULL,
  `pais` varchar(3) DEFAULT 'ARG',
  `nombre` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `fechahora` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`nro`),
  KEY `id` (`id`),
  KEY `pais` (`pais`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `config_bancos`
--

TRUNCATE TABLE `config_bancos`;
--
-- Volcado de datos para la tabla `config_bancos`
--

INSERT INTO `config_bancos` (`nro`, `id`, `pais`, `nombre`, `estado`, `fechahora`, `usuario_id`) VALUES
(1, 5, 'ARG', 'A.B.N. AMRO BANK N.V.', 'HAB', '2019-12-26 14:16:01', NULL),
(2, 7, 'ARG', 'BANCO DE GALICIA Y BUENOS AIRES S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(3, 11, 'ARG', 'BANCO DE LA NACI?N ARGENTINA', 'HAB', '2019-12-26 14:16:01', NULL),
(4, 14, 'ARG', 'BANCO DE LA PROVINCIA DE BUENOS AIRES', 'HAB', '2019-12-26 14:16:01', NULL),
(5, 15, 'ARG', 'STANDARD BANK ARGENTINA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(6, 16, 'ARG', 'CITIBANK N.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(7, 17, 'ARG', 'BBVA BANCO FRANC?S S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(8, 18, 'ARG', 'THE BANK OF TOKYO - MITSUBISHI UFJ, LTD.', 'HAB', '2019-12-26 14:16:01', NULL),
(9, 20, 'ARG', 'BANCO DE LA PROVINCIA DE CORDOBA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(10, 27, 'ARG', 'BANCO SUPERVIELLE S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(11, 29, 'ARG', 'BANCO DE LA CIUDAD DE BUENOS AIRES', 'HAB', '2019-12-26 14:16:01', NULL),
(12, 34, 'ARG', 'BANCO PATAGONIA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(13, 44, 'ARG', 'BANCO HIPOTECARIO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(14, 45, 'ARG', 'BANCO DE SAN JUAN S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(15, 46, 'ARG', 'BANCO DO BRASIL S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(16, 60, 'ARG', 'BANCO DEL TUCUMAN S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(17, 65, 'ARG', 'BANCO MUNICIPAL DE ROSARIO', 'HAB', '2019-12-26 14:16:01', NULL),
(18, 72, 'ARG', 'BANCO SANTANDER RIO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(19, 79, 'ARG', 'BANCO REGIONAL DE CUYO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(20, 83, 'ARG', 'BANCO DEL CHUBUT S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(21, 86, 'ARG', 'BANCO DE SANTA CRUZ S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(22, 93, 'ARG', 'BANCO DE LA PAMPA SOCIEDAD DE ECONOM?A MIXTA', 'HAB', '2019-12-26 14:16:01', NULL),
(23, 94, 'ARG', 'BANCO DE CORRIENTES S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(24, 97, 'ARG', 'BANCO PROVINCIA DEL NEUQU?N S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(25, 143, 'ARG', 'BRUBANK S.A.U.', 'HAB', '2020-12-22 15:02:18', NULL),
(26, 147, 'ARG', 'BANCO B. I. CREDITANSTALT S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(27, 150, 'ARG', 'HSBC BANK ARGENTINA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(28, 165, 'ARG', 'J P MORGAN CHASE BANK, NATIONAL ASSOCIATION (SUCURSAL BUENOS AIRES)', 'HAB', '2019-12-26 14:16:01', NULL),
(29, 191, 'ARG', 'BANCO CREDICOOP COOPERATIVO LIMITADO', 'HAB', '2019-12-26 14:16:01', NULL),
(30, 198, 'ARG', 'BANCO DE VALORES S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(31, 247, 'ARG', 'BANCO ROELA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(32, 254, 'ARG', 'BANCO MARIVA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(33, 259, 'ARG', 'BANCO ITAU BUEN AYRE S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(34, 262, 'ARG', 'BANK OF AMERICA, NATIONAL ASSOCIATION', 'HAB', '2019-12-26 14:16:01', NULL),
(35, 266, 'ARG', 'BNP PARIBAS', 'HAB', '2019-12-26 14:16:01', NULL),
(36, 268, 'ARG', 'BANCO PROVINCIA DE TIERRA DEL FUEGO', 'HAB', '2019-12-26 14:16:01', NULL),
(37, 269, 'ARG', 'BANCO DE LA REPUBLICA ORIENTAL DEL URUGUAY', 'HAB', '2019-12-26 14:16:01', NULL),
(38, 277, 'ARG', 'BANCO SAENZ S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(39, 281, 'ARG', 'BANCO MERIDIAN S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(40, 285, 'ARG', 'BANCO MACRO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(41, 293, 'ARG', 'BANCO MERCURIO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(42, 295, 'ARG', 'AMERICAN EXPRESS BANK LTD. S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(43, 299, 'ARG', 'BANCO COMAFI S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(44, 300, 'ARG', 'BANCO DE INVERSION Y COMERCIO EXTERIOR S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(45, 301, 'ARG', 'BANCO PIANO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(46, 303, 'ARG', 'BANCO FINANSUR S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(47, 305, 'ARG', 'BANCO JULIO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(48, 306, 'ARG', 'BANCO PRIVADO DE INVERSIONES S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(49, 309, 'ARG', 'NUEVO BANCO DE LA RIOJA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(50, 310, 'ARG', 'BANCO DEL SOL S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(51, 311, 'ARG', 'NUEVO BANCO DEL CHACO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(52, 312, 'ARG', 'M.B.A. BANCO DE INVERSIONES S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(53, 315, 'ARG', 'BANCO DE FORMOSA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(54, 319, 'ARG', 'BANCO CMF S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(55, 321, 'ARG', 'BANCO DE SANTIAGO DEL ESTERO S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(56, 322, 'ARG', 'NUEVO BANCO INDUSTRIAL DE AZUL S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(57, 325, 'ARG', 'DEUTSCHE BANK S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(58, 330, 'ARG', 'NUEVO BANCO DE SANTA FE S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(59, 331, 'ARG', 'BANCO CETELEM ARGENTINA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(60, 332, 'ARG', 'BANCO DE SERVICIOS FINANCIEROS S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(61, 335, 'ARG', 'BANCO COFIDIS S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(62, 336, 'ARG', 'BANCO BRADESCO ARGENTINA S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(63, 338, 'ARG', 'BANCO DE SERVICIOS Y TRANSACCIONES S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(64, 339, 'ARG', 'RCI BANQUE', 'HAB', '2019-12-26 14:16:01', NULL),
(65, 340, 'ARG', 'BACS BANCO DE CREDITO Y SECURITIZACION S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(66, 341, 'ARG', 'BANCO MASVENTAS S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(67, 384, 'ARG', 'WILOBANK S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(68, 386, 'ARG', 'NUEVO BANCO DE ENTRE RIOS S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(69, 388, 'ARG', 'NUEVO BANCO BISEL S.A.', 'HAB', '2019-12-26 14:16:01', NULL),
(70, 389, 'ARG', 'BANCO COLUMBIA S.A', 'HAB', '2019-12-26 14:16:01', NULL),
(71, 426, 'ARG', 'BANCO BICA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(72, 431, 'ARG', 'BANCO COINAG S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(73, 432, 'ARG', 'BANCO DE COMERCIO S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(74, 435, 'ARG', 'BANCO SUCREDITO REGIONAL S.A.U.', 'HAB', '2020-12-22 15:02:18', NULL),
(75, 448, 'ARG', 'BANCO DINO S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(76, 515, 'ARG', 'BANK OF CHINA LIMITED SUCURSAL BUENOS AI', 'HAB', '2020-12-22 15:02:18', NULL),
(77, 44059, 'ARG', 'FORD CREDIT COMPAÑIA FINANCIERA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(78, 44077, 'ARG', 'COMPAÑIA FINANCIERA ARGENTINA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(79, 44088, 'ARG', 'VOLKSWAGEN FINANCIAL SERVICES COMPAÑIA F', 'HAB', '2020-12-22 15:02:18', NULL),
(80, 44090, 'ARG', 'CORDIAL COMPAÑÍA FINANCIERA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(81, 44092, 'ARG', 'FCA COMPAÑIA FINANCIERA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(82, 44093, 'ARG', 'GPAT COMPAÑIA FINANCIERA S.A.U.', 'HAB', '2020-12-22 15:02:18', NULL),
(83, 44094, 'ARG', 'MERCEDES-BENZ COMPAÑÍA FINANCIERA ARGENT', 'HAB', '2020-12-22 15:02:18', NULL),
(84, 44095, 'ARG', 'ROMBO COMPAÑÍA FINANCIERA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(85, 44096, 'ARG', 'JOHN DEERE CREDIT COMPAÑÍA FINANCIERA S.', 'HAB', '2020-12-22 15:02:18', NULL),
(86, 44098, 'ARG', 'PSA FINANCE ARGENTINA COMPAÑÍA FINANCIER', 'HAB', '2020-12-22 15:02:18', NULL),
(87, 44099, 'ARG', 'TOYOTA COMPAÑÍA FINANCIERA DE ARGENTINA', 'HAB', '2020-12-22 15:02:18', NULL),
(88, 45056, 'ARG', 'MONTEMAR COMPAÑIA FINANCIERA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(89, 45072, 'ARG', 'TRANSATLANTICA COMPAÑIA FINANCIERA S.A.', 'HAB', '2020-12-22 15:02:18', NULL),
(90, 65203, 'ARG', 'CREDITO REGIONAL COMPAÑIA FINANCIERA S.A', 'HAB', '2020-12-22 15:02:18', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_notificador_logs`
--

DROP TABLE IF EXISTS `config_notificador_logs`;
CREATE TABLE IF NOT EXISTS `config_notificador_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fechahora` datetime NOT NULL,
  `notificador_id` int DEFAULT NULL,
  `request_data` text,
  `request_method` text,
  `response_code` int DEFAULT NULL,
  `response_data` text,
  `request_url` varchar(255) DEFAULT NULL,
  `curl_error` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `config_notificador_logs`
--

TRUNCATE TABLE `config_notificador_logs`;
--
-- Volcado de datos para la tabla `config_notificador_logs`
--

INSERT INTO `config_notificador_logs` (`id`, `fechahora`, `notificador_id`, `request_data`, `request_method`, `response_code`, `response_data`, `request_url`, `curl_error`) VALUES
(1, '2021-10-05 13:50:07', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446623494\"}', 'POST', NULL, '{\"pinId\":12345}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(2, '2021-10-05 13:52:54', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446623494\"}', 'POST', 200, '{\"pinId\":\"A9417FD478E15A3866A89F3F02CE3152\",\"to\":\"543446623494\",\"ncStatus\":\"NC_NOT_CONFIGURED\",\"smsStatus\":\"MESSAGE_SENT\"}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(3, '2021-10-05 13:57:02', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446314671\"}', 'POST', 200, '{\"pinId\":\"EDAA8635FFFA4C25C8EA862FADA5D839\",\"to\":\"543446314671\",\"ncStatus\":\"NC_NOT_CONFIGURED\",\"smsStatus\":\"MESSAGE_SENT\"}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(4, '2021-10-05 13:58:25', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446314670\"}', 'POST', 200, '{\"pinId\":\"95BFA6D0955493FEEF0C6E25FE32A5C8\",\"to\":\"543446314670\",\"ncStatus\":\"NC_NOT_CONFIGURED\",\"smsStatus\":\"MESSAGE_SENT\"}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(5, '2021-10-06 06:38:15', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446623494\"}', 'POST', 200, '{\"pinId\":\"136925C95DFB83D1451FCCDF160A39B6\",\"to\":\"543446623494\",\"ncStatus\":\"NC_NOT_CONFIGURED\",\"smsStatus\":\"MESSAGE_SENT\"}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(6, '2021-10-06 06:45:24', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446623494\"}', 'POST', 200, '{\"pinId\":\"3C351E9C8578D1CD5F616589CBE1EF5D\",\"to\":\"543446623494\",\"ncStatus\":\"NC_NOT_CONFIGURED\",\"smsStatus\":\"MESSAGE_SENT\"}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(7, '2021-10-06 13:42:35', 1, '{\"applicationId\":\"F4B61447766A1FFE53B85EAB42C0BABE\",\"messageId\":\"67DD8594785E792F8364CC4A9DE9ECD8\",\"from\":null,\"to\":\"543446623494\"}', 'POST', 200, '{\"pinId\":\"7BFAD9DFF5BC25112DBBB45F0E6EBC75\",\"to\":\"543446623494\",\"ncStatus\":\"NC_NOT_CONFIGURED\",\"smsStatus\":\"MESSAGE_SENT\"}', 'https://gn4xr.api.infobip.com/2fa/1/pin?ncNeeded=true', NULL),
(8, '2021-10-18 09:51:55', 1, '{\"pin\":\"11584\"}', 'POST', 400, '{\"requestError\":{\"serviceException\":{\"messageId\":\"BAD_REQUEST\",\"text\":\"Bad request\"}}}', 'https://gn4xr.api.infobip.com/2fa/1/pin/cea6058e549a2f57cacad8a8dde796f8/verify', NULL),
(9, '2021-10-18 10:01:33', 1, '{\"pin\":\"11584\"}', 'POST', 400, '{\"requestError\":{\"serviceException\":{\"messageId\":\"BAD_REQUEST\",\"text\":\"Bad request\"}}}', 'https://gn4xr.api.infobip.com/2fa/1/pin/cea6058e549a2f57cacad8a8dde796f8/verify', NULL),
(10, '2021-10-18 10:03:50', 1, '{\"pin\":\"53\"}', 'POST', 400, '{\"requestError\":{\"serviceException\":{\"messageId\":\"BAD_REQUEST\",\"text\":\"Bad request\"}}}', 'https://gn4xr.api.infobip.com/2fa/1/pin/request/verify', NULL),
(11, '2021-10-18 10:04:42', 1, '{\"pin\":\"11584\"}', 'POST', 400, '{\"requestError\":{\"serviceException\":{\"messageId\":\"BAD_REQUEST\",\"text\":\"Bad request\"}}}', 'https://gn4xr.api.infobip.com/2fa/1/pin/593b919f73884ae82f40227630b30ec6/verify', NULL),
(12, '2021-10-18 10:05:21', 1, '{\"pin\":\"11584\"}', 'POST', 400, '{\"requestError\":{\"serviceException\":{\"messageId\":\"BAD_REQUEST\",\"text\":\"Bad request\"}}}', 'https://gn4xr.api.infobip.com/2fa/1/pin/cea6058e549a2f57cacad8a8dde796f8/verify', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='Variables de configuración del sistema';

--
-- Truncar tablas antes de insertar `config_parametros`
--

TRUNCATE TABLE `config_parametros`;
--
-- Volcado de datos para la tabla `config_parametros`
--

INSERT INTO `config_parametros` (`id`, `grupo_id`, `nombre`, `valor`, `tipo`, `estado`, `permiso`, `descripcion`, `exponer`, `ofuscado`, `sys_fecha_modif`, `sys_fecha_alta`, `sys_usuario_id`) VALUES
(1, 1, 'body_encoding_key', '06d43abed4f73eefb6a85f3733c7661c550c49fd1422cd08b76acfcc42fc4686', 'STRING', 'HAB', '', 'Contraseña del cifrado simétrico entre el core y los clientes.', 1, 1, '2021-08-23 14:17:11', '2021-08-23 14:17:11', 1),
(2, 1, 'ws_users_secret_key', 'Extremelly secure encription key, really!', 'STRING', 'HAB', '', 'Conbraseña de cifrado de los tokens que se envían a los clientes del WS.', 1, 0, '2021-08-29 09:00:44', '2021-08-29 09:00:44', 1),
(4, 1, 'use_ban_list', '1', 'BOOL', 'HAB', '', 'Establece si usar o no la ban list.', 1, 0, '2021-09-09 13:29:44', '2021-09-09 13:29:44', 1),
(5, 1, 'moment_ban_list', 'pre', 'STRING', 'HAB', '', 'Si la ban list está activada en qué momento rechazar la solicitud.\r\nPRE: antes de crear la solicitud.\r\nPOST: al momento de aprobar la solicitud.\r\nMID: después de crear la solicitud.', 1, 0, '2021-09-09 13:30:18', '2021-09-09 13:30:18', 1),
(6, 4, 'infobip_test_tel_recipient', '543446623494', 'STRING', 'HAB', 'ADMIN', 'Número de teléfono que recibirá SMS de prueba cuando InfoBip este en modo Test', 0, 1, '2021-03-01 15:40:05', '2021-02-26 10:30:50', 1),
(7, 4, 'infobip_email_import_last_moment', '1587024903', 'STRING', 'HAB', '', NULL, 0, 1, '2020-04-16 05:15:03', '2020-04-03 11:51:20', NULL),
(8, 4, 'infobip_sms_import_last_moment', '1587024915', 'STRING', 'HAB', '', NULL, 0, 1, '2020-04-16 05:15:15', '2020-04-02 11:14:18', NULL),
(9, 4, 'infobip_template_id', '67DD8594785E792F8364CC4A9DE9ECD8', 'STRING', 'HAB', 'ADMIN', '', 0, 1, '2020-03-28 10:30:50', '2020-03-28 10:30:50', 1),
(10, 4, 'infobip_app_id', 'F4B61447766A1FFE53B85EAB42C0BABE', 'STRING', 'HAB', 'ADMIN', '', 0, 1, '2020-03-28 10:30:50', '2020-03-28 10:30:50', 1),
(11, 4, 'infobip_ws_url', 'https://gn4xr.api.infobip.com', 'STRING', 'HAB', 'ADMIN', '', 0, 1, '2020-03-28 10:30:50', '2020-03-28 10:30:50', 1),
(12, 4, 'infobip_password', 'ConsenTimIEnto46', 'STRING', 'HAB', 'ADMIN', '', 0, 1, '2020-03-28 10:30:50', '2020-03-28 10:30:50', 1),
(13, 4, 'infobip_username', 'OMBU', 'STRING', 'HAB', 'ADMIN', '', 0, 1, '2020-03-28 10:30:50', '2020-03-28 10:30:50', 1),
(14, 4, 'infobip_modo_test', '1', 'BOOL', 'HAB', 'OPER', 'Establece si el servicio de InfoBip se usa en modo \'test\'', 0, 0, '2021-05-06 08:42:59', '2020-01-09 14:50:51', 1),
(15, 5, 'fakeburo_username', 'jamesrandomhacker', 'STRING', 'HAB', '', 'Usuario del servicio', 0, 1, '2021-10-15 06:27:52', '2021-10-15 06:27:52', 1),
(16, 5, 'fakeburo_password', 'ouTpx287v6injcklZfyQew', 'STRING', 'HAB', '', 'La contraseña del usuario del servicio', 0, 1, '2021-10-15 06:27:52', '2021-10-15 06:27:52', 1),
(17, 5, 'fakeburo_api_url', 'http://fakeburo.local/v2/', 'STRING', 'HAB', '', 'La URL del web service.', 0, 0, '2021-10-15 06:30:15', '2021-10-15 06:30:15', 1),
(18, 5, 'fakeburo_use_ssl', '0', 'BOOL', 'HAB', '', 'La conexión es no no es a través de SSL', 0, 0, '2021-10-15 06:30:15', '2021-10-15 06:30:15', 1),
(19, 6, 'smspin_enabled', '1', 'BOOL', 'HAB', '', 'Habilitar el envío de PIN desde el frontend (esto desactiva la posibilidad de que el soliciante sea validado mediante PIN)', 1, 0, '2021-10-15 14:45:01', '2021-10-15 14:45:01', 1),
(20, 6, 'smspin_retryNumber', '3', 'INT', 'HAB', '', 'Número de reintentos que el visitante tiene para enviar PIN nuevo.', 1, 0, '2021-10-15 14:45:01', '2021-10-15 14:45:01', 1),
(21, 6, 'smspin_retryTimeout', '5', 'INT', 'HAB', '', 'Tiempo de espera (en segundos) antes de ofrecerle reintentar enviar un nuevo PIN.', 1, 0, '2021-10-15 14:45:01', '2021-10-15 14:45:01', NULL),
(22, 6, 'smspin_pinFormat', '\\d{5}', 'STRING', 'HAB', '', 'Formato esperado para el PIN (expresión regular)', 1, 0, '2021-10-15 14:45:01', '2021-10-15 14:45:01', 1),
(23, 7, 'email_modo_test', '1', 'BOOL', 'HAB', '', 'El envío se realiza en modo test.', 1, 0, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(24, 7, 'smtp_host', 'smtp.correoseguro.co', 'STRING', 'HAB', '', 'Dirección del servidor de correo.', 1, 1, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(25, 7, 'smtp_username', 'diego.romero@rebrit.ar', 'STRING', 'HAB', '', 'Nombre de usuario', 1, 1, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(26, 7, 'smtp_password', 'PWDjames5', 'STRING', 'HAB', '', 'Contraseña en el servidor de correo', 1, 1, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(27, 7, 'smtp_port', '587', 'INT', 'HAB', '', 'Puerto de conexión', 1, 1, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(28, 7, 'smtp_auth', '1', 'BOOL', 'HAB', '', 'El servidor requiere autorización', 1, 1, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(29, 7, 'smtp_secure', '1', 'BOOL', 'HAB', '', 'El servidor requiere conexión segura', 1, 1, '2021-10-19 12:48:36', '2021-10-19 12:48:36', 1),
(30, 7, 'test_email_recipient', 'diego.romero@rebrit.ar', 'STRING', 'HAB', '', 'Dirección de correo electrónico que recoge todas las pruebas.', 1, 0, '2021-10-19 12:55:22', '2021-10-19 12:55:22', 1),
(31, 7, 'email_bcc_test', '0', 'BOOL', 'HAB', '', 'Cuando se está en modo test, además de enviar el mensaje al destinatario de pruebas, enviar al destinatario original.', 1, 0, '2021-10-19 12:55:22', '2021-10-19 12:55:22', 1),
(32, 7, 'smtp_sender_address', 'diego.romero@rebrit.ar', 'STRING', 'HAB', '', 'Dirección de correo electrónico del remitente.', 1, 0, '2021-10-19 15:25:55', '2021-10-19 15:25:55', 1),
(33, 7, 'smtp_reply_to_address', 'nobody@rebrit.ar', 'STRING', 'HAB', '', 'Dirección de correo que recibirá la respuesta del receptor del correo enviado.', 1, 0, '2021-10-19 15:31:38', '2021-10-19 15:31:38', 1),
(34, 8, 'bind_ssl_key_file', 'client.key', 'STRING', 'HAB', NULL, 'Nombre del archivo de clave SSL.', 1, 0, '2021-05-08 08:01:25', '2021-05-07 17:58:10', 1),
(35, 8, 'bind_ssl_password', 'ZUTTGu838hqFKn', 'STRING', 'HAB', NULL, 'Contraseña del archivo de certificado SSL.', 1, 0, '2021-05-08 08:12:41', '2021-05-07 17:58:10', 1),
(36, 8, 'bind_ssl_cert_file', 'ombutech_prd.crt', 'STRING', 'HAB', NULL, 'Si la conexión a BIND es segura indicar el nombre del archivo de certificado SSL.', 1, 0, '2021-05-08 08:11:35', '2021-05-07 17:58:10', 1),
(37, 8, 'bind_use_ssl', '1', 'BOOL', 'HAB', '', 'Activar conexión por SSL.', 1, 0, '2021-05-07 17:58:10', '2021-05-07 17:58:10', 1),
(38, 8, 'bind_api_url', 'https://sandbox.bind.com.ar/v1/', 'STRING', 'HAB', NULL, 'La URL del web service de BIND.', 1, 0, '2020-09-22 08:57:51', '2020-09-21 13:35:00', 1),
(39, 8, 'bind_api_password', 'ViIVJ1V6gK5uaRT', 'STRING', 'HAB', NULL, 'La contraseña para usar en el servicio de BIND', 1, 0, '2020-09-22 08:57:33', '2020-09-21 13:35:00', 1),
(40, 8, 'bind_api_username', 'jballestero@ombutech.net', 'STRING', 'HAB', NULL, 'El nombre de usuario en el login del servicio de BIND', 1, 0, '2020-09-22 08:57:57', '2020-09-21 13:35:00', 1),
(41, 8, 'check_cbu_bind', '1', 'BOOL', 'HAB', NULL, 'Probar la existencia del CBU con el servicio de BIND.', 1, 0, '2020-09-22 08:58:04', '2020-09-21 13:35:00', 1),
(42, 1, 'tipos_cuentas_permitidos', 'CA,VIRTUAL', 'STRING', 'HAB', '', 'Tipos de cuentas permitidos para transferir el desembolso (separado con comas)', 1, 0, '2021-06-11 16:03:24', '2021-06-11 16:03:24', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `config_parametros_grupos`
--

TRUNCATE TABLE `config_parametros_grupos`;
--
-- Volcado de datos para la tabla `config_parametros_grupos`
--

INSERT INTO `config_parametros_grupos` (`id`, `nombre`, `estado`, `descripcion`, `fechahora_alta`, `fechahora_modif`, `usuario_id`) VALUES
(1, 'Sistema local', 'HAB', 'Configuraciones del sistema local.', '2021-08-23 14:16:39', '2021-08-23 14:16:39', 1),
(2, 'Flow de otorgamiento', 'HAB', 'Configuración que afecta el flow de otorgamiento del préstamo en el frontend.', '2021-08-30 12:53:08', '2021-08-30 12:53:08', 1),
(4, 'Infobip SMS', 'HAB', 'Servicio de envío de SMS.', '2021-10-05 10:51:33', '2021-10-05 10:51:33', 1),
(5, 'Fakeburó', 'HAB', 'Datos de conexión al buró de pruebas.', '2021-10-15 06:27:07', '2021-10-15 06:27:07', 1),
(6, 'SMS PIN', 'HAB', 'Configuración de las condiciones de generación del SMS PIN de cara al visitante.', '2021-10-15 14:43:45', '2021-10-15 14:43:45', 1),
(7, 'Email', 'HAB', 'Configuración general para el envío de correo electrónico.', '2021-10-19 12:47:59', '2021-10-19 12:47:59', 1),
(8, 'Bind', 'HAB', 'Opciones de la integración con BIND', '2021-10-21 11:37:13', '2021-10-21 11:37:13', 1);

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
-- Truncar tablas antes de insertar `core_contenidos`
--

TRUNCATE TABLE `core_contenidos`;
--
-- Volcado de datos para la tabla `core_contenidos`
--

INSERT INTO `core_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `descripcion`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1),
(2, 'inicio', 'Página de Inicio', 'pagina', '{\"js\": \"inicio,y,otras,cosas,bonitas\", \"css\": \"inicio\", \"vista\": \"inicio\"}', 0, 0, 1, 999, 0, 0, 0, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

DROP TABLE IF EXISTS `personas`;
CREATE TABLE IF NOT EXISTS `personas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int DEFAULT NULL COMMENT 'ID que se arrastra de cuando la persona era un solicitante',
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `tipo_doc` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'DNI',
  `nro_doc` varchar(32) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL COMMENT 'Fecha de nacimiento',
  `tel_movil` varchar(50) DEFAULT NULL,
  `dir` json DEFAULT NULL,
  `cpa` varchar(10) DEFAULT NULL,
  `ciudad_id` int DEFAULT '0',
  `ciudad_nombre` varchar(100) DEFAULT NULL,
  `region_id` int DEFAULT '0',
  `region_nombre` varchar(100) DEFAULT NULL,
  `pais_id` int DEFAULT NULL,
  `pais_nombre` varchar(100) DEFAULT 'Argentina',
  `data` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de alta del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de la última modificación',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'ID del usuario que genero el registro',
  PRIMARY KEY (`id`),
  KEY `nro_doc` (`nro_doc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas_data`
--

DROP TABLE IF EXISTS `personas_data`;
CREATE TABLE IF NOT EXISTS `personas_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int DEFAULT NULL COMMENT 'ID de la persona asignada al contacto',
  `tipo` varchar(12) DEFAULT NULL COMMENT 'Tipo de dato guardado',
  `valor` varchar(256) DEFAULT NULL COMMENT 'El valor guardado',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado del registro',
  `validado` tinyint DEFAULT NULL COMMENT 'Indica si el registro esta validado o no',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si este es el dato por omisión',
  `extras` json DEFAULT NULL COMMENT 'Cualquier otro dato asociado a este dato.',
  `duplicado` int DEFAULT NULL COMMENT 'ID de otra persona que tiene el mismo dato.',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de creación del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de última modificación del registro',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario que modifico por última ves el registro',
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Planes de crédito generados por el negocio';

--
-- Truncar tablas antes de insertar `planes`
--

TRUNCATE TABLE `planes`;
--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `negocio_id`, `marca_id`, `tipo`, `nombre_comercial`, `alias`, `esdefault`, `tipo_pagos`, `devengamiento`, `tasa_nominal_anual`, `tipo_moneda`, `score_minimo`, `score_maximo`, `vigencia_desde`, `vigencia_hasta`, `monto_minimo`, `monto_maximo`, `plazo_minimo`, `plazo_maximo`, `linea_credito_id`, `monto_fianza`, `data`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, NULL, 'PRESTAMO', 'Préstamo inicial', 'prestamo-inicial', 1, 'diario', 'frances', '150.0000', 'ARS', 100, 999, '2020-08-01', '2022-08-01', '5000.0000', '30000.0000', 7, 30, 1, NULL, '{\"step\": 500, \"TNA_Publico\": {\"valor\": 20, \"etiqueta\": \"Interés incluye IVA\"}, \"Costo_Publico\": {\"valor\": 80, \"etiqueta\": \"Costos administrativos incluye IVA\"}}', 'HAB', '2021-08-30 08:03:42', '2021-08-30 08:03:42', 1),
(2, 1, NULL, 'PRESTAMO', 'Préstamo personas especiales.', 'prestamo-especiales', 0, 'diario', 'frances', '120.0000', 'ARS', 100, 999, '2020-10-01', '2022-10-01', '3000.0000', '25000.0000', 3, 25, 1, NULL, '{\"step\": 500, \"TNA_Publico\": {\"valor\": 20, \"etiqueta\": \"Interés IVA incluido\"}, \"Costo_Publico\": {\"valor\": 80, \"etiqueta\": \"Costos administrativos IVA incluido\"}}', 'HAB', '2021-10-06 11:00:00', '2021-10-06 11:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `score_list`
--

DROP TABLE IF EXISTS `score_list`;
CREATE TABLE IF NOT EXISTS `score_list` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int DEFAULT NULL COMMENT 'ID de la persona',
  `buro_id` int DEFAULT NULL COMMENT 'ID del buró usado',
  `score` int DEFAULT NULL COMMENT 'El score crediticio',
  `data` json DEFAULT NULL COMMENT 'Cualquier otro dato tevuelto por el Buró',
  `fecha_request` date DEFAULT NULL COMMENT 'Fecha de la solicitud al buró',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  KEY `persona_id_2` (`persona_id`,`fecha_request`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Scores crediticios de las personas';

--
-- Truncar tablas antes de insertar `score_list`
--

TRUNCATE TABLE `score_list`;
--
-- Volcado de datos para la tabla `score_list`
--

INSERT INTO `score_list` (`id`, `persona_id`, `buro_id`, `score`, `data`, `fecha_request`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 1, 550, '{\"msg\": \"Success.\", \"data\": {\"dni\": 1000000, \"nro\": 57, \"piso\": null, \"calle\": \"Tra Blawgroaúqunsod\", \"depto\": null, \"score\": 550, \"nombre\": \"Regulo Baldomero\", \"apellido\": \"Lupo\", \"localidad\": \"Angelita\", \"provincia\": \"San Luis\", \"codigo_postal\": 5711, \"fecha_nacimiento\": \"1977-08-13\"}, \"mode\": \"N-A\", \"time\": 1634293080, \"errnro\": 0, \"tenant\": \"fakeburo\", \"transId\": null}', '2021-10-15', '2021-10-15 07:18:00', '2021-10-15 07:18:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

DROP TABLE IF EXISTS `solicitudes`;
CREATE TABLE IF NOT EXISTS `solicitudes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `negocio_id` int DEFAULT NULL COMMENT 'ID del negocio al que pertenece la solicitud',
  `estado` enum('HAB','DES','ELI') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'HAB' COMMENT 'Estado de validez de la solicitud',
  `estado_solicitud` enum('PEND','APRO','RECH','FAIL','ANUL') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'PEND' COMMENT 'Estado actual de la solicitud',
  `prestamo_id` int DEFAULT NULL COMMENT 'El ID del préstamo que fue generado por esta solicitud',
  `persona_id` int DEFAULT NULL COMMENT 'El ID de la persona asignada a esta solicitud',
  `data` json DEFAULT NULL,
  `cotizacion` json DEFAULT NULL COMMENT 'Datos de la cotización.',
  `origen` varchar(32) DEFAULT NULL COMMENT 'Desde donde provino la solicitud',
  `producto_id` int DEFAULT NULL COMMENT 'ID del producto si corresponde',
  `ws_usuario_id` int DEFAULT NULL COMMENT 'ID del usuario del webService que genero la solicitud',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de creación del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de última modificación del registro',
  `sys_usuario_id` datetime DEFAULT NULL COMMENT 'ID del usuario que realizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  KEY `prestamo_id` (`prestamo_id`),
  KEY `persona_id` (`persona_id`),
  KEY `sys_fecha_alta` (`sys_fecha_alta`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_log`
--

DROP TABLE IF EXISTS `solicitudes_log`;
CREATE TABLE IF NOT EXISTS `solicitudes_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solicitud_id` int DEFAULT NULL COMMENT 'ID de la solicitud de la cual es el log',
  `tipo` enum('ALL','DEBUG','INFO','WARN','ERROR','FATAL','OFF','TRACE') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'INFO' COMMENT 'Tipo de log',
  `texto` varchar(255) DEFAULT NULL COMMENT 'Texto descriptivo',
  `data` json DEFAULT NULL COMMENT 'Posibles datos que se insertaron en la solicitud',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de alta del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de la última modificación del registro',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario que realizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`)
) ENGINE=InnoDB AUTO_INCREMENT=601 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de usuarios de la API';

--
-- Truncar tablas antes de insertar `ws_usuarios`
--

TRUNCATE TABLE `ws_usuarios`;
--
-- Volcado de datos para la tabla `ws_usuarios`
--

INSERT INTO `ws_usuarios` (`id`, `negocio_id`, `username`, `password`, `config`, `modo`, `bodyencodingkey`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'mechawsuser', 'mj5raft3sl2', '{}', 'TEST', 'cec23ae6bf7b02dbc2b993e24397fedd414e04a2', '2021-08-29 09:07:02', '2021-08-29 09:07:02', 1),
(2, 1, 'DriverOp', 'estaeslapassword', NULL, 'TEST', NULL, '2021-10-12 07:29:53', '2021-10-12 07:29:53', 1),
(3, 1, 'lisandrows', 'pwdlisws', '{}', 'TEST', NULL, '2021-10-26 16:13:08', '2021-10-26 16:13:08', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
