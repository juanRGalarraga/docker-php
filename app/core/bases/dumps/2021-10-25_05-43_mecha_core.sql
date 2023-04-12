-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-10-2021 a las 05:43:04
-- Versión del servidor: 8.0.21
-- Versión de PHP: 7.4.9

SET FOREIGN_KEY_CHECKS=0;
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

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `solicitud_id`, `nombre`, `apellido`, `tipo_doc`, `nro_doc`, `email`, `fecha_nac`, `tel_movil`, `dir`, `cpa`, `ciudad_id`, `ciudad_nombre`, `region_id`, `region_nombre`, `pais_id`, `pais_nombre`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 16, 'Diego', 'Romero', 'DNI', '1000000', 'diego.romero@rebrit.ar', '1973-04-28', '3446-433008', '{\"nro\": \"1495\", \"calle\": \"Córdoba y los Chanaes\"}', 'E2822ABC', 8, 'Gualeguaychú', 8, 'Entre Ríos', 1, 'Argentina', '{\"ocupacion\": \"Programador\"}', '2021-10-01 11:17:53', '2021-10-01 11:17:53', 1);

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

--
-- Volcado de datos para la tabla `personas_data`
--

INSERT INTO `personas_data` (`id`, `persona_id`, `tipo`, `valor`, `estado`, `validado`, `default`, `extras`, `duplicado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'CBU', '3869904711100069478931', 'HAB', NULL, 1, '{\"banco\": \"Nuevo Banco de Entre Rios S.A.\", \"banco_id\": \"68\"}', NULL, '2021-10-04 09:39:25', '2021-10-04 09:39:25', 1),
(2, 1, 'EMAIL', 'driverop@gmail.com', 'HAB', 1, 0, NULL, NULL, '2021-10-04 10:43:04', '2021-10-04 10:43:04', 1),
(3, 1, 'EMAIL', 'diego.romero@rebrit.ar', 'HAB', 1, 1, NULL, NULL, '2021-10-04 10:55:20', '2021-10-04 10:55:20', 1),
(4, 1, 'TEL', '3446-623494', 'HAB', 1, 1, '{\"lugar\": \"celular\"}', NULL, '2021-10-04 10:56:55', '2021-10-04 10:56:55', 1),
(5, 1, 'TEL', '3446-436194', 'HAB', 1, 0, '{\"lugar\": \"casa\", \"responde\": \"Norma\"}', NULL, '2021-10-04 10:56:55', '2021-10-04 10:56:55', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id`, `negocio_id`, `estado`, `estado_solicitud`, `prestamo_id`, `persona_id`, `data`, `cotizacion`, `origen`, `producto_id`, `ws_usuario_id`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'HAB', 'PEND', NULL, NULL, '{\"email\": \"driverop@gmail.com\", \"plazo\": \"7\", \"origen\": \"FRONT\", \"capital\": \"5000\", \"paso_alias\": \"solicitalo-ahora\", \"rangeMonto\": \"5000\", \"rangePlazo\": \"7\", \"numberMonto\": \"5000\", \"numberPlazo\": \"7\", \"emailVerified\": true, \"codigoAceptacion\": \"qm1rh\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}', '{\"TNA\": \"30\", \"Dias\": 7, \"Tipo\": \"diario\", \"from\": \"COTIZ\", \"Total\": \"5708\", \"Planid\": \"1\", \"Capital\": \"5000\", \"Periodo\": \"7\", \"Porc_IVA\": \"21\", \"Intereses\": \"142\", \"Total_IVA\": \"60\", \"Etiqueta_1\": \"Interés incluye IVA\", \"Etiqueta_2\": \"Costos administrativos incluye IVA\", \"Fecha_Pago\": \"2021-10-27\", \"Tipo_Moneda\": \"Pesos Argentinos\", \"Monto_Maximo\": \"30000\", \"Monto_Minimo\": \"5000\", \"Plazo_Maximo\": \"30\", \"Plazo_Minimo\": \"7\", \"Score_Maximo\": \"999\", \"Score_Minimo\": \"100\", \"Simbolo_Moneda\": \"ARS\", \"Fecha_Pago_Display\": \"27/10/2021\", \"Gastos_Administrativos\": \"567\"}', 'FRONT', NULL, 1, '2021-10-20 09:18:56', '2021-10-20 12:50:32', NULL),
(2, 1, 'HAB', 'PEND', NULL, NULL, '{\"PIN\": \"12345\", \"cbu\": \"1910867811100088871164\", \"tel\": \"122-6543215\", \"plan\": \"1\", \"email\": \"eirhgeiorhguier@wiejfiwe.com\", \"optin\": \"on\", \"pinId\": \"74b72a47df9e9ff5784304c64dd574ec\", \"plazo\": \"14\", \"score\": \"550\", \"nombre\": \"Epifanio Guillermo\", \"origen\": \"FRONT\", \"telcod\": \"122\", \"telnum\": \"6543215\", \"capital\": \"25000\", \"nro_doc\": \"50520221\", \"apellido\": \"Miron\", \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"KUH8787y87ywefh8273fty\", \"retryPin\": 1, \"checkAuth\": \"on\", \"direccion\": {\"nro\": \"1\", \"calle\": \"A Tczñuxirlodkqóvmxc\"}, \"isTrusted\": \"1\", \"paso_alias\": \"resumen-prestamo\", \"rangeMonto\": \"25000\", \"rangePlazo\": \"14\", \"numberMonto\": \"25000\", \"numberPlazo\": \"14\", \"codigoAceptacion\": \"m7zc6\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}', '{\"TNA\": \"30\", \"Dias\": 14, \"Tipo\": \"diario\", \"from\": \"COTIZ\", \"Total\": \"31920\", \"Planid\": \"1\", \"Capital\": \"25000\", \"Periodo\": \"14\", \"Porc_IVA\": \"21\", \"Intereses\": \"1384\", \"Total_IVA\": \"569\", \"Etiqueta_1\": \"Interés incluye IVA\", \"Etiqueta_2\": \"Costos administrativos incluye IVA\", \"Fecha_Pago\": \"2021-11-03\", \"Tipo_Moneda\": \"Pesos Argentinos\", \"Monto_Maximo\": \"30000\", \"Monto_Minimo\": \"5000\", \"Plazo_Maximo\": \"30\", \"Plazo_Minimo\": \"7\", \"Score_Maximo\": \"999\", \"Score_Minimo\": \"100\", \"Simbolo_Moneda\": \"ARS\", \"Fecha_Pago_Display\": \"03/11/2021\", \"Gastos_Administrativos\": \"5536\"}', 'FRONT', NULL, 1, '2021-10-20 15:07:02', '2021-10-20 15:40:40', NULL),
(3, 1, 'HAB', 'PEND', NULL, NULL, '{\"PIN\": \"14535\", \"cbu\": \"0201227211100004413364\", \"tel\": \"123-4567890\", \"plan\": \"1\", \"email\": \"kwjeoifwjefoijiowe@wkejcoiwe.com\", \"optin\": \"on\", \"pinId\": \"40cc815d03a0a6c8211e65b4643e819c\", \"plazo\": \"13\", \"score\": \"420\", \"nombre\": \"Dafne Grisel\", \"origen\": \"FRONT\", \"telcod\": \"123\", \"telnum\": \"4567890\", \"capital\": \"27500\", \"nro_doc\": \"98765422\", \"apellido\": \"Quispe\", \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"654564DFWEEd232\", \"retryPin\": 1, \"checkAuth\": \"on\", \"direccion\": {\"nro\": \"153\", \"calle\": \"Carriego\"}, \"isTrusted\": \"1\", \"paso_alias\": \"crear-cuenta\", \"rangeMonto\": \"27500\", \"rangePlazo\": \"13\", \"numberMonto\": \"27500\", \"numberPlazo\": \"13\", \"emailVerified\": true, \"codigoAceptacion\": \"10mcg\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\", \"codigo_aceptacion\": \"10mcg\"}', '{\"TNA\": \"30\", \"Dias\": 13, \"Tipo\": \"diario\", \"from\": \"COTIZ\", \"Total\": \"34578\", \"Planid\": \"1\", \"Capital\": \"27500\", \"Periodo\": \"13\", \"Porc_IVA\": \"21\", \"Intereses\": \"1416\", \"Total_IVA\": \"583\", \"Etiqueta_1\": \"Interés incluye IVA\", \"Etiqueta_2\": \"Costos administrativos incluye IVA\", \"Fecha_Pago\": \"2021-11-03\", \"Tipo_Moneda\": \"Pesos Argentinos\", \"Monto_Maximo\": \"30000\", \"Monto_Minimo\": \"5000\", \"Plazo_Maximo\": \"30\", \"Plazo_Minimo\": \"7\", \"Score_Maximo\": \"999\", \"Score_Minimo\": \"100\", \"Simbolo_Moneda\": \"ARS\", \"Fecha_Pago_Display\": \"03/11/2021\", \"Gastos_Administrativos\": \"5662\"}', 'FRONT', NULL, 1, '2021-10-21 07:37:31', '2021-10-21 08:27:23', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=577 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `solicitudes_log`
--

INSERT INTO `solicitudes_log` (`id`, `solicitud_id`, `tipo`, `texto`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 14, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 10:38:33', '2021-10-01 10:38:33', NULL),
(2, 15, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 10:56:55', '2021-10-01 10:56:55', NULL),
(3, 15, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 11:06:38', '2021-10-01 11:06:38', NULL),
(4, 15, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"23051917\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As298398237\", \"checkAuth\": \"on\"}}', '2021-10-01 11:21:05', '2021-10-01 11:21:05', NULL),
(5, 15, 'WARN', 'Solicitud rechazada', '{\"nro_doc\": \"El dato se encuentra en la lista de exclusiones\"}', '2021-10-01 11:21:05', '2021-10-01 11:21:05', NULL),
(6, 16, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"13\", \"origen\": \"FRONT\", \"capital\": \"26000\", \"rangeMonto\": \"26000\", \"rangePlazo\": \"13\", \"numberMonto\": \"26000\", \"numberPlazo\": \"13\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 13:55:55', '2021-10-01 13:55:55', NULL),
(7, 16, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:02:29', '2021-10-01 14:02:29', NULL),
(8, 16, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:02:32', '2021-10-01 14:02:32', NULL),
(9, 16, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:02:37', '2021-10-01 14:02:37', NULL),
(10, 16, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"\", \"checkPdP\": \"0\", \"checkTyc\": \"0\", \"password\": \"\", \"checkAuth\": \"0\"}}', '2021-10-01 14:02:38', '2021-10-01 14:02:38', NULL),
(11, 16, 'ERROR', 'Solicitud modificada', '{\"nro_doc\": \"no puede estar vacío\", \"password\": \"La contraseña no puede estar vacía\"}', '2021-10-01 14:02:38', '2021-10-01 14:02:38', NULL),
(12, 16, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"23051917\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"wefwjehfIh87\", \"checkAuth\": \"on\"}}', '2021-10-01 14:03:15', '2021-10-01 14:03:15', NULL),
(13, 16, 'WARN', 'Solicitud rechazada', '{\"nro_doc\": \"El dato se encuentra en la lista de exclusiones\"}', '2021-10-01 14:03:15', '2021-10-01 14:03:15', NULL),
(14, 17, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 14:33:12', '2021-10-01 14:33:12', NULL),
(15, 18, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 14:33:45', '2021-10-01 14:33:45', NULL),
(16, 18, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:33:46', '2021-10-01 14:33:46', NULL),
(17, 19, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 14:34:52', '2021-10-01 14:34:52', NULL),
(18, 19, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:34:53', '2021-10-01 14:34:53', NULL),
(19, 19, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:35:45', '2021-10-01 14:35:45', NULL),
(20, 19, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:35:47', '2021-10-01 14:35:47', NULL),
(21, 20, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 14:36:06', '2021-10-01 14:36:06', NULL),
(22, 20, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:36:07', '2021-10-01 14:36:07', NULL),
(23, 20, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:36:23', '2021-10-01 14:36:23', NULL),
(24, 20, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:36:24', '2021-10-01 14:36:24', NULL),
(25, 21, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 14:36:41', '2021-10-01 14:36:41', NULL),
(26, 21, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:36:42', '2021-10-01 14:36:42', NULL),
(27, 22, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 14:49:16', '2021-10-01 14:49:16', NULL),
(28, 22, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 14:49:17', '2021-10-01 14:49:17', NULL),
(29, 23, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 15:03:26', '2021-10-01 15:03:26', NULL),
(30, 23, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:03:27', '2021-10-01 15:03:27', NULL),
(31, 24, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 15:04:34', '2021-10-01 15:04:34', NULL),
(32, 24, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:04:37', '2021-10-01 15:04:37', NULL),
(33, 25, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 15:06:07', '2021-10-01 15:06:07', NULL),
(34, 25, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:06:09', '2021-10-01 15:06:09', NULL),
(35, 25, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:06:30', '2021-10-01 15:06:30', NULL),
(36, 25, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:08:46', '2021-10-01 15:08:46', NULL),
(37, 26, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 15:20:07', '2021-10-01 15:20:07', NULL),
(38, 26, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:20:23', '2021-10-01 15:20:23', NULL),
(39, 27, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-01 15:22:31', '2021-10-01 15:22:31', NULL),
(40, 27, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-01 15:22:33', '2021-10-01 15:22:33', NULL),
(41, 28, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"28\", \"origen\": \"FRONT\", \"capital\": \"25000\", \"rangeMonto\": \"25000\", \"rangePlazo\": \"28\", \"numberMonto\": \"25000\", \"numberPlazo\": \"28\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-02 06:39:38', '2021-10-02 06:39:38', NULL),
(42, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:39:39', '2021-10-02 06:39:39', NULL),
(43, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"8984654\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"65489erokger9ig\", \"checkAuth\": \"on\"}}', '2021-10-02 06:39:50', '2021-10-02 06:39:50', NULL),
(44, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:39:50', '2021-10-02 06:39:50', NULL),
(45, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:41:30', '2021-10-02 06:41:30', NULL),
(46, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:44:35', '2021-10-02 06:44:35', NULL),
(47, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"456-9876543\", \"email\": \"gj3io4jg3i4j@elkrvoerijg.vo\", \"optin\": \"on\", \"telcod\": \"456\", \"telnum\": \"9876543\"}}', '2021-10-02 06:45:06', '2021-10-02 06:45:06', NULL),
(48, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:45:06', '2021-10-02 06:45:06', NULL),
(49, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:46:20', '2021-10-02 06:46:20', NULL),
(50, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 06:58:29', '2021-10-02 06:58:29', NULL),
(51, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:19:33', '2021-10-02 07:19:33', NULL),
(52, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"12364\"}}', '2021-10-02 07:46:33', '2021-10-02 07:46:33', NULL),
(53, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:46:33', '2021-10-02 07:46:33', NULL),
(54, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"35432\"}}', '2021-10-02 07:48:11', '2021-10-02 07:48:11', NULL),
(55, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:48:11', '2021-10-02 07:48:11', NULL),
(56, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"65432\"}}', '2021-10-02 07:48:46', '2021-10-02 07:48:46', NULL),
(57, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:48:46', '2021-10-02 07:48:46', NULL),
(58, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"12354\"}}', '2021-10-02 07:49:44', '2021-10-02 07:49:44', NULL),
(59, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:49:44', '2021-10-02 07:49:44', NULL),
(60, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"78954\"}}', '2021-10-02 07:50:15', '2021-10-02 07:50:15', NULL),
(61, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:50:15', '2021-10-02 07:50:15', NULL),
(62, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"98765\"}}', '2021-10-02 07:54:30', '2021-10-02 07:54:30', NULL),
(63, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:54:30', '2021-10-02 07:54:30', NULL),
(64, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 07:54:36', '2021-10-02 07:54:36', NULL),
(65, 28, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"0170075640000077148090\", \"checkDeb\": \"on\"}}', '2021-10-02 08:12:00', '2021-10-02 08:12:00', NULL),
(66, 28, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 08:12:00', '2021-10-02 08:12:00', NULL),
(67, 29, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-02 10:08:56', '2021-10-02 10:08:56', NULL),
(68, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:08:58', '2021-10-02 10:08:58', NULL),
(69, 29, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"6548521\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"Aieurhfuier8767\", \"checkAuth\": \"on\"}}', '2021-10-02 10:09:09', '2021-10-02 10:09:09', NULL),
(70, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:09:09', '2021-10-02 10:09:09', NULL),
(71, 29, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"223-5551545\", \"email\": \"kurhiuehriufe@xdnifuneiufw.com\", \"optin\": \"on\", \"telcod\": \"223\", \"telnum\": \"5551545\"}}', '2021-10-02 10:09:23', '2021-10-02 10:09:23', NULL),
(72, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:09:23', '2021-10-02 10:09:23', NULL),
(73, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:09:24', '2021-10-02 10:09:24', NULL),
(74, 29, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-02 10:09:27', '2021-10-02 10:09:27', NULL),
(75, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:09:27', '2021-10-02 10:09:27', NULL),
(76, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:09:28', '2021-10-02 10:09:28', NULL),
(77, 29, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"2625848511100086347302\", \"checkDeb\": \"on\"}}', '2021-10-02 10:09:33', '2021-10-02 10:09:33', NULL),
(78, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:09:33', '2021-10-02 10:09:33', NULL),
(79, 29, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"10\", \"capital\": \"30000\", \"rangeMonto\": \"30000\", \"rangePlazo\": \"10\", \"numberMonto\": \"30000\", \"numberPlazo\": \"10\"}}', '2021-10-02 10:28:50', '2021-10-02 10:28:50', NULL),
(80, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:28:50', '2021-10-02 10:28:50', NULL),
(81, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:32:39', '2021-10-02 10:32:39', NULL),
(82, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:33:13', '2021-10-02 10:33:13', NULL),
(83, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:34:18', '2021-10-02 10:34:18', NULL),
(84, 29, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"1438834711100086040935\"}}', '2021-10-02 10:35:16', '2021-10-02 10:35:16', NULL),
(85, 29, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 10:35:16', '2021-10-02 10:35:16', NULL),
(86, 30, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-02 19:01:43', '2021-10-02 19:01:43', NULL),
(87, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:01:51', '2021-10-02 19:01:51', NULL),
(88, 30, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"6548975\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"Aswuhefiuwehf8767\", \"checkAuth\": \"on\"}}', '2021-10-02 19:02:01', '2021-10-02 19:02:01', NULL),
(89, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:02:01', '2021-10-02 19:02:01', NULL),
(90, 30, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"477-3435536\", \"email\": \"weufhiwuehfiuw@wkejfiow.com\", \"optin\": \"on\", \"telcod\": \"477\", \"telnum\": \"3435536\"}}', '2021-10-02 19:02:17', '2021-10-02 19:02:17', NULL),
(91, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:02:17', '2021-10-02 19:02:17', NULL),
(92, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:02:20', '2021-10-02 19:02:20', NULL),
(93, 30, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-02 19:02:23', '2021-10-02 19:02:23', NULL),
(94, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:02:23', '2021-10-02 19:02:23', NULL),
(95, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:02:28', '2021-10-02 19:02:28', NULL),
(96, 30, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"2625848511100086347302\", \"checkDeb\": \"on\"}}', '2021-10-02 19:02:41', '2021-10-02 19:02:41', NULL),
(97, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:02:41', '2021-10-02 19:02:41', NULL),
(98, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:04:01', '2021-10-02 19:04:01', NULL),
(99, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:09:16', '2021-10-02 19:09:16', NULL),
(100, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:10:08', '2021-10-02 19:10:08', NULL),
(101, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:11:28', '2021-10-02 19:11:28', NULL),
(102, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:14:18', '2021-10-02 19:14:18', NULL),
(103, 30, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-02 19:15:05', '2021-10-02 19:15:05', NULL),
(104, 31, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"25\", \"origen\": \"FRONT\", \"capital\": \"11000\", \"rangeMonto\": \"11000\", \"rangePlazo\": \"25\", \"numberMonto\": \"11000\", \"numberPlazo\": \"25\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-03 14:40:32', '2021-10-03 14:40:32', NULL),
(105, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:40:36', '2021-10-03 14:40:36', NULL),
(106, 31, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"4582212\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"uiweoug8787\", \"checkAuth\": \"on\"}}', '2021-10-03 14:40:58', '2021-10-03 14:40:58', NULL),
(107, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:40:58', '2021-10-03 14:40:58', NULL),
(108, 31, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"233-3454733\", \"email\": \"werjhwiuefhuiwhef@dkfnviuerjigue.com\", \"optin\": \"on\", \"telcod\": \"233\", \"telnum\": \"3454733\"}}', '2021-10-03 14:41:35', '2021-10-03 14:41:35', NULL),
(109, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:41:35', '2021-10-03 14:41:35', NULL),
(110, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:41:40', '2021-10-03 14:41:40', NULL),
(111, 31, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-03 14:41:43', '2021-10-03 14:41:43', NULL),
(112, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:41:43', '2021-10-03 14:41:43', NULL),
(113, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:41:48', '2021-10-03 14:41:48', NULL),
(114, 31, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"2625848511100086347302\", \"checkDeb\": \"on\"}}', '2021-10-03 14:41:54', '2021-10-03 14:41:54', NULL),
(115, 31, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-03 14:41:54', '2021-10-03 14:41:54', NULL),
(116, 32, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-04 15:17:16', '2021-10-04 15:17:16', NULL),
(117, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:17:18', '2021-10-04 15:17:18', NULL),
(118, 32, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"37564938\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As129387487234\", \"checkAuth\": \"on\"}}', '2021-10-04 15:17:30', '2021-10-04 15:17:30', NULL),
(119, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:17:30', '2021-10-04 15:17:30', NULL),
(120, 32, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"366-6848450\", \"email\": \"iuuwfuywgefwe@nebgyuberyg.com\", \"optin\": \"on\", \"telcod\": \"366\", \"telnum\": \"6848450\"}}', '2021-10-04 15:17:51', '2021-10-04 15:17:51', NULL),
(121, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:17:51', '2021-10-04 15:17:51', NULL),
(122, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:17:56', '2021-10-04 15:17:56', NULL),
(123, 32, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-04 15:17:59', '2021-10-04 15:17:59', NULL),
(124, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:17:59', '2021-10-04 15:17:59', NULL),
(125, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:18:04', '2021-10-04 15:18:04', NULL),
(126, 32, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478931\", \"checkDeb\": \"on\"}}', '2021-10-04 15:19:37', '2021-10-04 15:19:37', NULL),
(127, 32, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:19:37', '2021-10-04 15:19:37', NULL),
(128, 32, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478930\"}}', '2021-10-04 15:20:58', '2021-10-04 15:20:58', NULL),
(129, 32, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:20:58', '2021-10-04 15:20:58', NULL),
(130, 33, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-04 15:21:48', '2021-10-04 15:21:48', NULL),
(131, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:21:50', '2021-10-04 15:21:50', NULL),
(132, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"37564938\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As129387487234\", \"checkAuth\": \"on\"}}', '2021-10-04 15:21:57', '2021-10-04 15:21:57', NULL),
(133, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:21:57', '2021-10-04 15:21:57', NULL),
(134, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"366-6848450\", \"email\": \"iuuwfuywgefwe@nebgyuberyg.com\", \"optin\": \"on\", \"telcod\": \"366\", \"telnum\": \"6848450\"}}', '2021-10-04 15:22:00', '2021-10-04 15:22:00', NULL),
(135, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:22:00', '2021-10-04 15:22:00', NULL),
(136, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:22:11', '2021-10-04 15:22:11', NULL),
(137, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-04 15:22:17', '2021-10-04 15:22:17', NULL),
(138, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:22:17', '2021-10-04 15:22:17', NULL),
(139, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:22:22', '2021-10-04 15:22:22', NULL),
(140, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478931\", \"checkDeb\": \"on\"}}', '2021-10-04 15:22:29', '2021-10-04 15:22:29', NULL),
(141, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:22:29', '2021-10-04 15:22:29', NULL),
(142, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:22:31', '2021-10-04 15:22:31', NULL),
(143, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:22:31', '2021-10-04 15:22:31', NULL),
(144, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478938\"}}', '2021-10-04 15:22:37', '2021-10-04 15:22:37', NULL),
(145, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:22:37', '2021-10-04 15:22:37', NULL),
(146, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478930\", \"plazo\": \"28\", \"capital\": \"11000\", \"rangeMonto\": \"11000\", \"rangePlazo\": \"28\", \"numberMonto\": \"11000\", \"numberPlazo\": \"28\"}}', '2021-10-04 15:23:17', '2021-10-04 15:23:17', NULL),
(147, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:23:17', '2021-10-04 15:23:17', NULL),
(148, 33, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:23:37', '2021-10-04 15:23:37', NULL),
(149, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478931\"}}', '2021-10-04 15:24:01', '2021-10-04 15:24:01', NULL),
(150, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:24:01', '2021-10-04 15:24:01', NULL),
(151, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:24:06', '2021-10-04 15:24:06', NULL),
(152, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"email\": \"\", \"telcod\": \"\", \"telnum\": \"\"}}', '2021-10-04 15:24:54', '2021-10-04 15:24:54', NULL),
(153, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\", \"email\": \"El email no puede estar vacío\"}', '2021-10-04 15:24:54', '2021-10-04 15:24:54', NULL),
(154, 33, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"email\": \"iuuwfuywgefwe@nebgyuberyg.com\", \"telcod\": \"366\", \"telnum\": \"6848450\"}}', '2021-10-04 15:31:11', '2021-10-04 15:31:11', NULL),
(155, 33, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:31:11', '2021-10-04 15:31:11', NULL),
(156, 34, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-04 15:31:37', '2021-10-04 15:31:37', NULL),
(157, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:31:39', '2021-10-04 15:31:39', NULL),
(158, 34, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"37564938\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As129387487234\", \"checkAuth\": \"on\"}}', '2021-10-04 15:31:41', '2021-10-04 15:31:41', NULL),
(159, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:31:41', '2021-10-04 15:31:41', NULL),
(160, 34, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"366-6848450\", \"email\": \"iuuwfuywgefwe@nebgyuberyg.com\", \"optin\": \"on\", \"telcod\": \"366\", \"telnum\": \"6848450\"}}', '2021-10-04 15:31:43', '2021-10-04 15:31:43', NULL),
(161, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:31:43', '2021-10-04 15:31:43', NULL),
(162, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:31:44', '2021-10-04 15:31:44', NULL),
(163, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:32:02', '2021-10-04 15:32:02', NULL),
(164, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:32:03', '2021-10-04 15:32:03', NULL),
(165, 34, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-04 15:32:07', '2021-10-04 15:32:07', NULL),
(166, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:32:07', '2021-10-04 15:32:07', NULL),
(167, 34, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:32:08', '2021-10-04 15:32:08', NULL),
(168, 34, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478931\", \"checkDeb\": \"on\"}}', '2021-10-04 15:32:09', '2021-10-04 15:32:09', NULL),
(169, 34, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:32:09', '2021-10-04 15:32:09', NULL),
(170, 34, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-04 15:32:16', '2021-10-04 15:32:16', NULL),
(171, 35, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-04 15:32:44', '2021-10-04 15:32:44', NULL),
(172, 35, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:32:45', '2021-10-04 15:32:45', NULL),
(173, 35, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"37564938\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As129387487234\", \"checkAuth\": \"on\"}}', '2021-10-04 15:32:50', '2021-10-04 15:32:50', NULL),
(174, 35, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-04 15:32:50', '2021-10-04 15:32:50', NULL),
(175, 36, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-05 05:52:40', '2021-10-05 05:52:40', NULL),
(176, 36, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 05:58:17', '2021-10-05 05:58:17', NULL),
(177, 36, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"\", \"checkPdP\": \"0\", \"checkTyc\": \"0\", \"password\": \"\", \"checkAuth\": \"0\"}}', '2021-10-05 06:46:20', '2021-10-05 06:46:20', NULL),
(178, 36, 'ERROR', 'Solicitud modificada', '{\"nro_doc\": \"no puede estar vacío\", \"password\": \"La contraseña no puede estar vacía\"}', '2021-10-05 06:46:20', '2021-10-05 06:46:20', NULL),
(179, 37, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-05 07:18:24', '2021-10-05 07:18:24', NULL),
(180, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 07:18:26', '2021-10-05 07:18:26', NULL),
(181, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"11111111\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"12421342jhuhj\", \"checkAuth\": \"on\"}}', '2021-10-05 09:03:19', '2021-10-05 09:03:19', NULL),
(182, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:03:19', '2021-10-05 09:03:19', NULL),
(183, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"456-9876553\", \"email\": \"oiyjriotgiuerhtg@ekjfoier.co\", \"optin\": \"on\", \"telcod\": \"456\", \"telnum\": \"9876553\"}}', '2021-10-05 09:03:41', '2021-10-05 09:03:41', NULL),
(184, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:03:41', '2021-10-05 09:03:41', NULL),
(185, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"2625848511100086347302\", \"checkDeb\": \"on\"}}', '2021-10-05 09:03:53', '2021-10-05 09:03:53', NULL),
(186, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:03:53', '2021-10-05 09:03:53', NULL),
(187, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478930\"}}', '2021-10-05 09:04:02', '2021-10-05 09:04:02', NULL),
(188, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:04:02', '2021-10-05 09:04:02', NULL),
(189, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478931\"}}', '2021-10-05 09:04:06', '2021-10-05 09:04:06', NULL),
(190, 37, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-05 09:04:06', '2021-10-05 09:04:06', NULL),
(191, 37, 'ERROR', 'Solicitud modificada', '{\"cbu\": \"Este CBU ya pertenece a otra persona\"}', '2021-10-05 09:05:11', '2021-10-05 09:05:11', NULL),
(192, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478930\"}}', '2021-10-05 09:06:23', '2021-10-05 09:06:23', NULL),
(193, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:06:23', '2021-10-05 09:06:23', NULL),
(194, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:07:08', '2021-10-05 09:07:08', NULL),
(195, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"alias\": \"datos-personales\"}}', '2021-10-05 09:19:53', '2021-10-05 09:19:53', NULL),
(196, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:19:53', '2021-10-05 09:19:53', NULL),
(197, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\"}}', '2021-10-05 09:22:51', '2021-10-05 09:22:51', NULL),
(198, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:22:51', '2021-10-05 09:22:51', NULL),
(199, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:22:57', '2021-10-05 09:22:57', NULL),
(200, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:26:17', '2021-10-05 09:26:17', NULL),
(201, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"codigo-pin\"}}', '2021-10-05 09:30:59', '2021-10-05 09:30:59', NULL),
(202, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:30:59', '2021-10-05 09:30:59', NULL),
(203, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\"}}', '2021-10-05 09:31:06', '2021-10-05 09:31:06', NULL),
(204, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:31:06', '2021-10-05 09:31:06', NULL),
(205, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresar-cbu\"}}', '2021-10-05 09:31:29', '2021-10-05 09:31:29', NULL),
(206, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:31:29', '2021-10-05 09:31:29', NULL),
(207, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:32:28', '2021-10-05 09:32:28', NULL),
(208, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"telefono-mail\"}}', '2021-10-05 09:34:37', '2021-10-05 09:34:37', NULL),
(209, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:34:37', '2021-10-05 09:34:37', NULL),
(210, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"crear-cuenta\"}}', '2021-10-05 09:34:43', '2021-10-05 09:34:43', NULL),
(211, 37, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 09:34:43', '2021-10-05 09:34:43', NULL),
(212, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"9788-987542\", \"email\": \"driverop@gmail.com\", \"telcod\": \"9788\", \"telnum\": \"987542\", \"paso_alias\": \"telefono-mail\"}}', '2021-10-05 09:34:53', '2021-10-05 09:34:53', NULL),
(213, 37, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-05 09:34:53', '2021-10-05 09:34:53', NULL),
(214, 37, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-05 09:35:58', '2021-10-05 09:35:58', NULL),
(215, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"datos-personales\"}}', '2021-10-05 09:36:03', '2021-10-05 09:36:03', NULL),
(216, 37, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-05 09:36:03', '2021-10-05 09:36:03', NULL),
(217, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"84802\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-05 09:36:46', '2021-10-05 09:36:46', NULL),
(218, 37, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-05 09:36:46', '2021-10-05 09:36:46', NULL),
(219, 37, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-05 09:36:52', '2021-10-05 09:36:52', NULL),
(220, 37, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-05 09:36:52', '2021-10-05 09:36:52', NULL),
(221, 38, 'INFO', 'Solicitud iniciada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-05 15:08:03', '2021-10-05 15:08:03', NULL),
(222, 38, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-05 15:08:04', '2021-10-05 15:08:04', NULL),
(223, 38, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 15:08:04', '2021-10-05 15:08:04', NULL),
(224, 38, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-05 15:08:06', '2021-10-05 15:08:06', NULL),
(225, 38, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-05 15:08:06', '2021-10-05 15:08:06', NULL),
(226, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 08:49:04', '2021-10-06 08:49:04', NULL),
(227, 39, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 08:49:04', '2021-10-06 08:49:04', NULL),
(228, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-06 08:49:04', '2021-10-06 08:49:04', NULL),
(229, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 08:49:04', '2021-10-06 08:49:04', NULL),
(230, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-06 08:49:12', '2021-10-06 08:49:12', NULL),
(231, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 08:49:12', '2021-10-06 08:49:12', NULL),
(232, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nro_doc\": \"8998465\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"6549845g45g4\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-06 08:49:25', '2021-10-06 08:49:25', NULL),
(233, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 08:49:25', '2021-10-06 08:49:25', NULL),
(234, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"25\", \"capital\": \"9500\", \"rangeMonto\": \"9500\", \"rangePlazo\": \"25\", \"numberMonto\": \"9500\", \"numberPlazo\": \"25\"}}', '2021-10-06 08:59:50', '2021-10-06 08:59:50', NULL),
(235, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 08:59:50', '2021-10-06 08:59:50', NULL),
(236, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"656-3216548\", \"email\": \"flkvnoweirhjvwioer@skjdvoiwjev.copm\", \"optin\": \"on\", \"telcod\": \"656\", \"telnum\": \"3216548\", \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-06 09:00:31', '2021-10-06 09:00:31', NULL),
(237, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 09:00:31', '2021-10-06 09:00:31', NULL),
(238, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"datos-personales\", \"paso_descripcion\": \"El visitante confirma sus datos antes de proceder a la verificación de identidad, se presentan los datos del buró\"}}', '2021-10-06 09:02:51', '2021-10-06 09:02:51', NULL),
(239, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 09:02:51', '2021-10-06 09:02:51', NULL),
(240, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-06 09:03:07', '2021-10-06 09:03:07', NULL),
(241, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 09:03:07', '2021-10-06 09:03:07', NULL),
(242, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-06 09:03:12', '2021-10-06 09:03:12', NULL),
(243, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 09:03:12', '2021-10-06 09:03:12', NULL),
(244, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3869904711100069478930\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-06 09:03:52', '2021-10-06 09:03:52', NULL),
(245, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 09:03:52', '2021-10-06 09:03:52', NULL),
(246, 39, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"capital\": \"22000\", \"paso_alias\": \"crear-cuenta\", \"rangeMonto\": \"22000\", \"numberMonto\": \"22000\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-06 09:06:53', '2021-10-06 09:06:53', NULL),
(247, 39, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 09:06:53', '2021-10-06 09:06:53', NULL),
(248, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 11:03:38', '2021-10-06 11:03:38', NULL),
(249, 40, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 11:03:38', '2021-10-06 11:03:38', NULL),
(250, 40, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-06 11:03:38', '2021-10-06 11:03:38', NULL),
(251, 40, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 11:03:38', '2021-10-06 11:03:38', NULL),
(252, 40, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"2\", \"plazo\": \"12\", \"capital\": \"11500\", \"paso_alias\": \"ingresa-cuenta\", \"rangeMonto\": \"11500\", \"rangePlazo\": \"12\", \"numberMonto\": \"11500\", \"numberPlazo\": \"12\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-06 11:07:04', '2021-10-06 11:07:04', NULL),
(253, 40, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 11:07:04', '2021-10-06 11:07:04', NULL),
(254, 40, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"18\", \"capital\": \"7000\", \"rangeMonto\": \"7000\", \"rangePlazo\": \"18\", \"numberMonto\": \"7000\", \"numberPlazo\": \"18\"}}', '2021-10-06 11:07:17', '2021-10-06 11:07:17', NULL),
(255, 40, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 11:07:17', '2021-10-06 11:07:17', NULL),
(256, 0, 'ERROR', 'Solicitud iniciada', '{\"nro_doc\": \"Ya existe una persona con este número de documento\"}', '2021-10-06 12:55:16', '2021-10-06 12:55:16', NULL),
(257, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 12:55:38', '2021-10-06 12:55:38', NULL),
(258, 41, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"18\", \"capital\": \"25000\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 12:55:38', '2021-10-06 12:55:38', NULL),
(259, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 12:56:20', '2021-10-06 12:56:20', NULL),
(260, 42, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"18\", \"capital\": \"25000\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 12:56:20', '2021-10-06 12:56:20', NULL),
(261, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 12:59:49', '2021-10-06 12:59:49', NULL),
(262, 43, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"18\", \"capital\": \"25000\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 12:59:49', '2021-10-06 12:59:49', NULL),
(263, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:00:01', '2021-10-06 13:00:01', NULL),
(264, 44, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plan\": \"1\", \"plazo\": \"18\", \"capital\": \"25000\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 13:00:01', '2021-10-06 13:00:01', NULL),
(265, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:00:12', '2021-10-06 13:00:12', NULL),
(266, 45, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plan\": \"2\", \"plazo\": \"18\", \"capital\": \"25000\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 13:00:12', '2021-10-06 13:00:12', NULL),
(267, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:00:36', '2021-10-06 13:00:36', NULL),
(268, 46, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plan\": \"2\", \"plazo\": \"18\", \"capital\": \"5600\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 13:00:36', '2021-10-06 13:00:36', NULL),
(269, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:36:17', '2021-10-06 13:36:17', NULL),
(270, 47, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plan\": \"2\", \"plazo\": \"18\", \"capital\": \"5600\", \"nro_doc\": \"23051917\", \"password\": \"As123456!\"}, \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 13:36:17', '2021-10-06 13:36:17', NULL),
(271, 47, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"0202020811100065939779\", \"email\": \"gaston.fernandez@rebrit.ar\", \"nombre\": \"Gastón\", \"apellido\": \"Fernandez\", \"password\": \"123456789aA!\"}}', '2021-10-06 13:36:30', '2021-10-06 13:36:30', NULL),
(272, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:36:30', '2021-10-06 13:36:30', NULL),
(273, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:38:08', '2021-10-06 13:38:08', NULL),
(274, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:40:21', '2021-10-06 13:40:21', NULL),
(275, 47, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"capital\": 6500}}', '2021-10-06 13:40:34', '2021-10-06 13:40:34', NULL),
(276, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:40:34', '2021-10-06 13:40:34', NULL),
(277, 47, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": 15}}', '2021-10-06 13:40:54', '2021-10-06 13:40:54', NULL),
(278, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:40:54', '2021-10-06 13:40:54', NULL),
(279, 47, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"email\": \"tonga@gmail.ar\", \"nombre\": \"Tonga\", \"apellido\": \"Ferdinando\"}}', '2021-10-06 13:41:46', '2021-10-06 13:41:46', NULL),
(280, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:41:46', '2021-10-06 13:41:46', NULL),
(281, 47, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": 1}}', '2021-10-06 13:42:08', '2021-10-06 13:42:08', NULL),
(282, 47, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 13:42:08', '2021-10-06 13:42:08', NULL),
(283, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:42:26', '2021-10-06 15:42:26', NULL),
(284, 48, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"17\", \"origen\": \"FRONT\", \"capital\": \"5000\", \"rangeMonto\": \"5000\", \"rangePlazo\": \"17\", \"numberMonto\": \"5000\", \"numberPlazo\": \"17\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 15:42:26', '2021-10-06 15:42:26', NULL),
(285, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:48:47', '2021-10-06 15:48:47', NULL),
(286, 49, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"17\", \"origen\": \"FRONT\", \"capital\": \"5000\", \"rangeMonto\": \"5000\", \"rangePlazo\": \"17\", \"numberMonto\": \"5000\", \"numberPlazo\": \"17\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-06 15:48:47', '2021-10-06 15:48:47', NULL),
(287, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-06 15:48:47', '2021-10-06 15:48:47', NULL),
(288, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:48:47', '2021-10-06 15:48:47', NULL),
(289, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-06 15:49:25', '2021-10-06 15:49:25', NULL),
(290, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:49:25', '2021-10-06 15:49:25', NULL);
INSERT INTO `solicitudes_log` (`id`, `solicitud_id`, `tipo`, `texto`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(291, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"capital\": \"19000\", \"nro_doc\": \"23051918\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"efwefwe32323\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"rangeMonto\": \"19000\", \"numberMonto\": \"19000\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-06 15:49:56', '2021-10-06 15:49:56', NULL),
(292, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:49:56', '2021-10-06 15:49:56', NULL),
(293, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"456-5146540\", \"email\": \"oirjgoiejrgoier@skjfwoiejfoi.com\", \"optin\": \"on\", \"telcod\": \"456\", \"telnum\": \"5146540\", \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-06 15:50:32', '2021-10-06 15:50:32', NULL),
(294, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:50:32', '2021-10-06 15:50:32', NULL),
(295, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"13\", \"paso_alias\": \"datos-personales\", \"rangePlazo\": \"13\", \"numberPlazo\": \"13\", \"paso_descripcion\": \"El visitante confirma sus datos antes de proceder a la verificación de identidad, se presentan los datos del buró\"}}', '2021-10-06 15:50:46', '2021-10-06 15:50:46', NULL),
(296, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:50:46', '2021-10-06 15:50:46', NULL),
(297, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"36986\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-06 15:51:31', '2021-10-06 15:51:31', NULL),
(298, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:51:31', '2021-10-06 15:51:31', NULL),
(299, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-06 15:51:36', '2021-10-06 15:51:36', NULL),
(300, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:51:36', '2021-10-06 15:51:36', NULL),
(301, 49, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"0170075640000077148090\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-06 15:52:47', '2021-10-06 15:52:47', NULL),
(302, 49, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-06 15:52:47', '2021-10-06 15:52:47', NULL),
(303, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-14 07:38:24', '2021-10-14 07:38:24', NULL),
(304, 50, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"14\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"14\", \"numberMonto\": \"17500\", \"numberPlazo\": \"14\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-14 07:38:24', '2021-10-14 07:38:24', NULL),
(305, 50, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-14 07:38:24', '2021-10-14 07:38:24', NULL),
(306, 50, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-14 07:38:24', '2021-10-14 07:38:24', NULL),
(307, 50, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-14 07:38:30', '2021-10-14 07:38:30', NULL),
(308, 50, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-14 07:38:30', '2021-10-14 07:38:30', NULL),
(309, 50, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"9876542\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As23647826374\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-14 07:38:42', '2021-10-14 07:38:42', NULL),
(310, 50, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-14 07:38:42', '2021-10-14 07:38:42', NULL),
(311, 50, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"456-4655601\", \"email\": \"driverop@gmail.com\", \"optin\": \"on\", \"telcod\": \"456\", \"telnum\": \"4655601\", \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-14 07:39:00', '2021-10-14 07:39:00', NULL),
(312, 50, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-14 07:39:00', '2021-10-14 07:39:00', NULL),
(313, 50, 'ERROR', 'Solicitud modificada', '{\"email\": \"Este email ya pertenece a otra persona\"}', '2021-10-14 07:41:59', '2021-10-14 07:41:59', NULL),
(314, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 07:47:34', '2021-10-15 07:47:34', NULL),
(315, 51, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-15 07:47:34', '2021-10-15 07:47:34', NULL),
(316, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-15 07:47:34', '2021-10-15 07:47:34', NULL),
(317, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 07:47:34', '2021-10-15 07:47:34', NULL),
(318, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-15 07:47:36', '2021-10-15 07:47:36', NULL),
(319, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 07:47:36', '2021-10-15 07:47:36', NULL),
(320, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"45678912\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As178247523674\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-15 07:47:48', '2021-10-15 07:47:48', NULL),
(321, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 07:47:48', '2021-10-15 07:47:48', NULL),
(322, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"66-65498720\", \"email\": \"eoruighuriuehriughe@wiejfioejf.com\", \"optin\": \"on\", \"telcod\": \"66\", \"telnum\": \"65498720\", \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-15 07:48:07', '2021-10-15 07:48:07', NULL),
(323, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 07:48:07', '2021-10-15 07:48:07', NULL),
(324, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:33:32', '2021-10-15 08:33:32', NULL),
(325, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"nombre\": \"Nahir Emerita\", \"apellido\": \"Giay\", \"direccion\": \"Zwsbyóssounloh\"}}', '2021-10-15 08:39:16', '2021-10-15 08:39:16', NULL),
(326, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:39:16', '2021-10-15 08:39:16', NULL),
(327, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"datos-personales\", \"paso_descripcion\": \"El visitante confirma sus datos antes de proceder a la verificación de identidad, se presentan los datos del buró\"}}', '2021-10-15 08:39:44', '2021-10-15 08:39:44', NULL),
(328, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:39:44', '2021-10-15 08:39:44', NULL),
(329, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-15 08:41:21', '2021-10-15 08:41:21', NULL),
(330, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:41:21', '2021-10-15 08:41:21', NULL),
(331, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"score\": \"670\"}}', '2021-10-15 08:47:39', '2021-10-15 08:47:39', NULL),
(332, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:47:39', '2021-10-15 08:47:39', NULL),
(333, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:50:04', '2021-10-15 08:50:04', NULL),
(334, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:50:29', '2021-10-15 08:50:29', NULL),
(335, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 08:55:34', '2021-10-15 08:55:34', NULL),
(336, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"0647\", \"calle\": \"Zwsbyóssounloh\"}}}', '2021-10-15 09:00:12', '2021-10-15 09:00:12', NULL),
(337, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 09:00:12', '2021-10-15 09:00:12', NULL),
(338, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"0647\", \"calle\": \"Zwsbyóssounloh\"}}}', '2021-10-15 09:01:28', '2021-10-15 09:01:28', NULL),
(339, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 09:01:28', '2021-10-15 09:01:28', NULL),
(340, 51, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"datos-personales\", \"paso_descripcion\": \"El visitante confirma sus datos antes de proceder a la verificación de identidad, se presentan los datos del buró\"}}', '2021-10-15 09:01:37', '2021-10-15 09:01:37', NULL),
(341, 51, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 09:01:37', '2021-10-15 09:01:37', NULL),
(342, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 14:15:59', '2021-10-15 14:15:59', NULL),
(343, 52, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"25\", \"origen\": \"FRONT\", \"capital\": \"11500\", \"rangeMonto\": \"11500\", \"rangePlazo\": \"25\", \"numberMonto\": \"11500\", \"numberPlazo\": \"25\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-15 14:15:59', '2021-10-15 14:15:59', NULL),
(344, 52, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-15 14:15:59', '2021-10-15 14:15:59', NULL),
(345, 52, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 14:15:59', '2021-10-15 14:15:59', NULL),
(346, 52, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-15 14:16:00', '2021-10-15 14:16:00', NULL),
(347, 52, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 14:16:00', '2021-10-15 14:16:00', NULL),
(348, 52, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"98765421\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"skjhfuewH827634782\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-15 14:16:14', '2021-10-15 14:16:14', NULL),
(349, 52, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-15 14:16:14', '2021-10-15 14:16:14', NULL),
(350, 52, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"3446-623494\", \"email\": \"esteesmimail@wkefiuwhefui.com\", \"optin\": \"on\", \"score\": \"470\", \"nombre\": \"Fortuna Aurora\", \"telcod\": \"3446\", \"telnum\": \"623494\", \"apellido\": \"David\", \"direccion\": {\"nro\": \"3364\", \"calle\": \"Óogmtdrhst\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-15 14:16:33', '2021-10-15 14:16:33', NULL),
(351, 52, 'ERROR', 'Solicitud modificada', '{\"tel\": \"Este tel ya pertenece a otra persona\"}', '2021-10-15 14:16:33', '2021-10-15 14:16:33', NULL),
(352, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:32:34', '2021-10-16 10:32:34', NULL),
(353, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:34:03', '2021-10-16 10:34:03', NULL),
(354, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:37:09', '2021-10-16 10:37:09', NULL),
(355, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:39:26', '2021-10-16 10:39:26', NULL),
(356, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:40:14', '2021-10-16 10:40:14', NULL),
(357, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:41:45', '2021-10-16 10:41:45', NULL),
(358, 51, 'DEBUG', 'Solicitud modificada', '{\"pinId\": \"457f610143f8fc6b3bf31c65c45209a3\"}', '2021-10-16 10:42:09', '2021-10-16 10:42:09', NULL),
(359, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 06:58:34', '2021-10-18 06:58:34', NULL),
(360, 53, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"24\", \"origen\": \"FRONT\", \"capital\": \"12000\", \"rangeMonto\": \"12000\", \"rangePlazo\": \"24\", \"numberMonto\": \"12000\", \"numberPlazo\": \"24\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-18 06:58:35', '2021-10-18 06:58:35', NULL),
(361, 53, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-18 06:58:35', '2021-10-18 06:58:35', NULL),
(362, 53, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 06:58:35', '2021-10-18 06:58:35', NULL),
(363, 53, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-18 06:58:36', '2021-10-18 06:58:36', NULL),
(364, 53, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 06:58:36', '2021-10-18 06:58:36', NULL),
(365, 53, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"6549705\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"65484ERFWE\\\"·wecw\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-18 06:59:02', '2021-10-18 06:59:02', NULL),
(366, 53, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 06:59:03', '2021-10-18 06:59:03', NULL),
(367, 53, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"3234-654321\", \"email\": \"eirjgij3oi4joi3j4@wlknvoiejf.com\", \"optin\": \"on\", \"score\": \"410\", \"nombre\": \"Amadeo Melibeo\", \"telcod\": \"3234\", \"telnum\": \"654321\", \"apellido\": \"Santander\", \"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-18 06:59:24', '2021-10-18 06:59:24', NULL),
(368, 53, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 06:59:24', '2021-10-18 06:59:24', NULL),
(369, 53, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 07:00:55', '2021-10-18 07:00:55', NULL),
(370, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:17:37', '2021-10-18 10:17:37', NULL),
(371, 54, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-18 10:17:37', '2021-10-18 10:17:37', NULL),
(372, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-18 10:17:37', '2021-10-18 10:17:37', NULL),
(373, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:17:37', '2021-10-18 10:17:37', NULL),
(374, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-18 10:17:38', '2021-10-18 10:17:38', NULL),
(375, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:17:38', '2021-10-18 10:17:38', NULL),
(376, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"plazo\": \"24\", \"capital\": \"12000\", \"nro_doc\": \"6549705\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"wkuehfuw\\\"·ERWEVW33434\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"rangeMonto\": \"12000\", \"rangePlazo\": \"24\", \"numberMonto\": \"12000\", \"numberPlazo\": \"24\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-18 10:18:16', '2021-10-18 10:18:16', NULL),
(377, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:18:16', '2021-10-18 10:18:16', NULL),
(378, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"3234-654321\", \"email\": \"eirjgij3oi4joi3j4@wlknvoiejf.com\", \"optin\": \"on\", \"score\": \"410\", \"nombre\": \"Amadeo Melibeo\", \"telcod\": \"3234\", \"telnum\": \"654321\", \"apellido\": \"Santander\", \"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-18 10:18:51', '2021-10-18 10:18:51', NULL),
(379, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:18:51', '2021-10-18 10:18:51', NULL),
(380, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:18:56', '2021-10-18 10:18:56', NULL),
(381, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-18 10:19:22', '2021-10-18 10:19:22', NULL),
(382, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:19:22', '2021-10-18 10:19:22', NULL),
(383, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-18 10:19:27', '2021-10-18 10:19:27', NULL),
(384, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:19:27', '2021-10-18 10:19:27', NULL),
(385, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:20:14', '2021-10-18 10:20:14', NULL),
(386, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-18 10:23:47', '2021-10-18 10:23:47', NULL),
(387, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:23:47', '2021-10-18 10:23:47', NULL),
(388, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-18 10:23:53', '2021-10-18 10:23:53', NULL),
(389, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:23:53', '2021-10-18 10:23:53', NULL),
(390, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"2625848511100086347302\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-18 10:23:57', '2021-10-18 10:23:57', NULL),
(391, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:23:57', '2021-10-18 10:23:57', NULL),
(392, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"15\", \"capital\": \"18000\"}}', '2021-10-18 10:40:14', '2021-10-18 10:40:14', NULL),
(393, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:40:14', '2021-10-18 10:40:14', NULL),
(394, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}, \"paso_alias\": \"telefono-mail\", \"rangeMonto\": \"18000\", \"rangePlazo\": \"15\", \"numberMonto\": \"18000\", \"numberPlazo\": \"15\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-18 10:43:47', '2021-10-18 10:43:47', NULL),
(395, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:43:47', '2021-10-18 10:43:47', NULL),
(396, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 10:43:47', '2021-10-18 10:43:47', NULL),
(397, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:43:47', '2021-10-18 10:43:47', NULL),
(398, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:43:48', '2021-10-18 10:43:48', NULL),
(399, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 10:43:48', '2021-10-18 10:43:48', NULL),
(400, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 11:06:05', '2021-10-18 11:06:05', NULL),
(401, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:06:05', '2021-10-18 11:06:05', NULL),
(402, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:06:06', '2021-10-18 11:06:06', NULL),
(403, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 11:08:18', '2021-10-18 11:08:18', NULL),
(404, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:08:18', '2021-10-18 11:08:18', NULL),
(405, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:08:19', '2021-10-18 11:08:19', NULL),
(406, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 11:09:16', '2021-10-18 11:09:16', NULL),
(407, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:09:16', '2021-10-18 11:09:16', NULL),
(408, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:09:20', '2021-10-18 11:09:20', NULL),
(409, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 11:51:52', '2021-10-18 11:51:52', NULL),
(410, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:51:52', '2021-10-18 11:51:52', NULL),
(411, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"3234-885222\", \"telnum\": \"885222\", \"retryPin\": \"0\", \"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 11:52:20', '2021-10-18 11:52:20', NULL),
(412, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:52:20', '2021-10-18 11:52:20', NULL),
(413, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 11:52:41', '2021-10-18 11:52:41', NULL),
(414, 54, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"8721\", \"calle\": \"Íebhlctgsacgwye\"}}}', '2021-10-18 12:02:17', '2021-10-18 12:02:17', NULL),
(415, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 12:02:17', '2021-10-18 12:02:17', NULL),
(416, 54, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-18 12:02:18', '2021-10-18 12:02:18', NULL),
(417, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:17:19', '2021-10-19 06:17:19', NULL),
(418, 55, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"20\", \"origen\": \"FRONT\", \"capital\": \"15000\", \"rangeMonto\": \"15000\", \"rangePlazo\": \"20\", \"numberMonto\": \"15000\", \"numberPlazo\": \"20\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-19 06:17:19', '2021-10-19 06:17:19', NULL),
(419, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-19 06:17:19', '2021-10-19 06:17:19', NULL),
(420, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:17:19', '2021-10-19 06:17:19', NULL),
(421, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-19 06:17:20', '2021-10-19 06:17:20', NULL),
(422, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:17:20', '2021-10-19 06:17:20', NULL),
(423, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:22:09', '2021-10-19 06:22:09', NULL),
(424, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:24:11', '2021-10-19 06:24:11', NULL),
(425, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:25:38', '2021-10-19 06:25:38', NULL),
(426, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:31:05', '2021-10-19 06:31:05', NULL),
(427, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:32:05', '2021-10-19 06:32:05', NULL),
(428, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"5520123\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"ygfytVVERf34r3\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-19 06:34:23', '2021-10-19 06:34:23', NULL),
(429, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:34:23', '2021-10-19 06:34:23', NULL),
(430, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"63-52210012\", \"email\": \"eroigeiurhguheiurhg@lwkjnfoiwejfo.com\", \"optin\": \"on\", \"score\": \"490\", \"nombre\": \"Ovidio Heber\", \"telcod\": \"63\", \"telnum\": \"52210012\", \"apellido\": \"Della Negra\", \"direccion\": {\"nro\": \"3349\", \"calle\": \"Jrwogmñj\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-19 06:34:39', '2021-10-19 06:34:39', NULL),
(431, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:34:39', '2021-10-19 06:34:39', NULL),
(432, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:34:56', '2021-10-19 06:34:56', NULL),
(433, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:36:50', '2021-10-19 06:36:50', NULL),
(434, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 06:55:39', '2021-10-19 06:55:39', NULL),
(435, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 07:01:14', '2021-10-19 07:01:14', NULL),
(436, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 07:04:50', '2021-10-19 07:04:50', NULL),
(437, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 07:05:05', '2021-10-19 07:05:05', NULL),
(438, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"65420\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-19 08:03:09', '2021-10-19 08:03:09', NULL),
(439, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 08:03:09', '2021-10-19 08:03:09', NULL),
(440, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-19 08:03:14', '2021-10-19 08:03:14', NULL),
(441, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 08:03:14', '2021-10-19 08:03:14', NULL),
(442, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3401522311100008687745\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-19 08:04:05', '2021-10-19 08:04:05', NULL),
(443, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 08:04:05', '2021-10-19 08:04:05', NULL),
(444, 55, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"isTrusted\": \"1\", \"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-19 08:15:31', '2021-10-19 08:15:31', NULL),
(445, 55, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 08:15:31', '2021-10-19 08:15:31', NULL),
(446, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:51:52', '2021-10-19 11:51:52', NULL),
(447, 56, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"20\", \"origen\": \"FRONT\", \"capital\": \"15000\", \"rangeMonto\": \"15000\", \"rangePlazo\": \"20\", \"numberMonto\": \"15000\", \"numberPlazo\": \"20\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-19 11:51:52', '2021-10-19 11:51:52', NULL),
(448, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-19 11:51:52', '2021-10-19 11:51:52', NULL),
(449, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:51:52', '2021-10-19 11:51:52', NULL),
(450, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-19 11:51:54', '2021-10-19 11:51:54', NULL),
(451, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:51:54', '2021-10-19 11:51:54', NULL),
(452, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"98765422\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"qljwnd7328237·\\\"F$\\\"·\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-19 11:52:03', '2021-10-19 11:52:03', NULL),
(453, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:52:03', '2021-10-19 11:52:03', NULL),
(454, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"337-6655882\", \"email\": \"swkejbwiuehfuw@wiejfowiejfiwe.com\", \"score\": \"420\", \"nombre\": \"Dafne Grisel\", \"telcod\": \"337\", \"telnum\": \"6655882\", \"apellido\": \"Quispe\", \"direccion\": {\"nro\": \"153\", \"calle\": \"Carriego\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-19 11:52:22', '2021-10-19 11:52:22', NULL),
(455, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:52:22', '2021-10-19 11:52:22', NULL),
(456, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:52:35', '2021-10-19 11:52:35', NULL),
(457, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"65492\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-19 11:52:41', '2021-10-19 11:52:41', NULL),
(458, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:52:41', '2021-10-19 11:52:41', NULL),
(459, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-19 11:52:47', '2021-10-19 11:52:47', NULL),
(460, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:52:47', '2021-10-19 11:52:47', NULL),
(461, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"3194233911100099116685\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-19 11:52:59', '2021-10-19 11:52:59', NULL),
(462, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:52:59', '2021-10-19 11:52:59', NULL),
(463, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"isTrusted\": \"1\", \"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-19 11:53:10', '2021-10-19 11:53:10', NULL),
(464, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:53:10', '2021-10-19 11:53:10', NULL),
(465, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-19 11:57:19', '2021-10-19 11:57:19', NULL),
(466, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 11:57:19', '2021-10-19 11:57:19', NULL),
(467, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-19 12:07:09', '2021-10-19 12:07:09', NULL),
(468, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:07:09', '2021-10-19 12:07:09', NULL),
(469, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-19 12:10:00', '2021-10-19 12:10:00', NULL),
(470, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:10:00', '2021-10-19 12:10:00', NULL),
(471, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-19 12:10:01', '2021-10-19 12:10:01', NULL),
(472, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:10:01', '2021-10-19 12:10:01', NULL),
(473, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-19 12:10:02', '2021-10-19 12:10:02', NULL),
(474, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:10:02', '2021-10-19 12:10:02', NULL),
(475, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-19 12:10:12', '2021-10-19 12:10:12', NULL),
(476, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:10:12', '2021-10-19 12:10:12', NULL),
(477, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"direccion\": {\"nro\": \"153\", \"calle\": \"Carriego\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-19 12:12:09', '2021-10-19 12:12:09', NULL),
(478, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:12:09', '2021-10-19 12:12:09', NULL),
(479, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:12:10', '2021-10-19 12:12:10', NULL),
(480, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"12345\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-19 12:12:14', '2021-10-19 12:12:14', NULL),
(481, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:12:14', '2021-10-19 12:12:14', NULL),
(482, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-19 12:12:15', '2021-10-19 12:12:15', NULL),
(483, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:12:15', '2021-10-19 12:12:15', NULL),
(484, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-19 12:12:16', '2021-10-19 12:12:16', NULL),
(485, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:12:16', '2021-10-19 12:12:16', NULL),
(486, 56, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-19 12:14:05', '2021-10-19 12:14:05', NULL),
(487, 56, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-19 12:14:05', '2021-10-19 12:14:05', NULL),
(488, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:21:48', '2021-10-20 05:21:48', NULL),
(489, 57, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 05:21:48', '2021-10-20 05:21:48', NULL),
(490, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-20 05:21:48', '2021-10-20 05:21:48', NULL),
(491, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:21:48', '2021-10-20 05:21:48', NULL),
(492, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-20 05:21:49', '2021-10-20 05:21:49', NULL),
(493, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:21:49', '2021-10-20 05:21:49', NULL),
(494, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"6549872\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"iefuiwhefuUhu87\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-20 05:22:06', '2021-10-20 05:22:06', NULL),
(495, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:22:06', '2021-10-20 05:22:06', NULL),
(496, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"654-6549871\", \"email\": \"wlkejfwiejofijw@woijefoiwjef.com\", \"optin\": \"on\", \"score\": \"530\", \"nombre\": \"Macarena Dolores\", \"telcod\": \"654\", \"telnum\": \"6549871\", \"apellido\": \"Concha\", \"direccion\": {\"nro\": \"493\", \"calle\": \"Dhtokqtxr\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-20 05:22:22', '2021-10-20 05:22:22', NULL),
(497, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:22:22', '2021-10-20 05:22:22', NULL),
(498, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:22:24', '2021-10-20 05:22:24', NULL),
(499, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"12345\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-20 05:22:28', '2021-10-20 05:22:28', NULL),
(500, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:22:28', '2021-10-20 05:22:28', NULL),
(501, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-20 05:22:29', '2021-10-20 05:22:29', NULL),
(502, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:22:29', '2021-10-20 05:22:29', NULL),
(503, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"0448445511100028785681\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-20 05:22:46', '2021-10-20 05:22:46', NULL),
(504, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:22:46', '2021-10-20 05:22:46', NULL),
(505, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"10\", \"capital\": \"20000\", \"isTrusted\": \"1\", \"paso_alias\": \"resumen-prestamo\", \"rangeMonto\": \"20000\", \"rangePlazo\": \"10\", \"numberMonto\": \"20000\", \"numberPlazo\": \"10\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-20 05:23:06', '2021-10-20 05:23:06', NULL),
(506, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 05:23:06', '2021-10-20 05:23:06', NULL),
(507, 57, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"18\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}}', '2021-10-20 08:25:32', '2021-10-20 08:25:32', NULL),
(508, 57, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:25:32', '2021-10-20 08:25:32', NULL),
(509, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:35:12', '2021-10-20 08:35:12', NULL),
(510, 58, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"PIN\": \"84802\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 08:35:12', '2021-10-20 08:35:12', NULL),
(511, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:35:14', '2021-10-20 08:35:14', NULL),
(512, 59, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"PIN\": \"84802\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 08:35:14', '2021-10-20 08:35:14', NULL),
(513, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:35:19', '2021-10-20 08:35:19', NULL),
(514, 60, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"PIN\": \"98785\", \"plazo\": \"18\", \"origen\": \"FRONT\", \"capital\": \"17500\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 08:35:19', '2021-10-20 08:35:19', NULL),
(515, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:35:26', '2021-10-20 08:35:26', NULL),
(516, 61, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"PIN\": \"98785\", \"plazo\": \"19\", \"origen\": \"FRONT\", \"capital\": \"18000\", \"rangeMonto\": \"18000\", \"rangePlazo\": \"19\", \"numberMonto\": \"18000\", \"numberPlazo\": \"19\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 08:35:26', '2021-10-20 08:35:26', NULL),
(517, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:36:43', '2021-10-20 08:36:43', NULL),
(518, 62, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"22\", \"origen\": \"FRONT\", \"capital\": \"21000\", \"rangeMonto\": \"21000\", \"rangePlazo\": \"22\", \"numberMonto\": \"21000\", \"numberPlazo\": \"22\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 08:36:43', '2021-10-20 08:36:43', NULL),
(519, 62, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-20 08:36:43', '2021-10-20 08:36:43', NULL),
(520, 62, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:36:43', '2021-10-20 08:36:43', NULL),
(521, 62, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plazo\": \"18\", \"capital\": \"17500\", \"paso_alias\": \"ingresa-cuenta\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-20 08:36:44', '2021-10-20 08:36:44', NULL),
(522, 62, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 08:36:44', '2021-10-20 08:36:44', NULL),
(523, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 09:18:56', '2021-10-20 09:18:56', NULL),
(524, 1, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"7\", \"origen\": \"FRONT\", \"capital\": \"5000\", \"rangeMonto\": \"5000\", \"rangePlazo\": \"7\", \"numberMonto\": \"5000\", \"numberPlazo\": \"7\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 09:18:56', '2021-10-20 09:18:56', NULL),
(525, 1, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-20 09:18:56', '2021-10-20 09:18:56', NULL),
(526, 1, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 09:18:56', '2021-10-20 09:18:56', NULL),
(527, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:07:02', '2021-10-20 15:07:02', NULL),
(528, 2, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"14\", \"origen\": \"FRONT\", \"capital\": \"25000\", \"rangeMonto\": \"25000\", \"rangePlazo\": \"14\", \"numberMonto\": \"25000\", \"numberPlazo\": \"14\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-20 15:07:02', '2021-10-20 15:07:02', NULL),
(529, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-20 15:07:02', '2021-10-20 15:07:02', NULL),
(530, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:07:02', '2021-10-20 15:07:02', NULL),
(531, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-20 15:07:04', '2021-10-20 15:07:04', NULL),
(532, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:07:04', '2021-10-20 15:07:04', NULL),
(533, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"50520221\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"KUH8787y87ywefh8273fty\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-20 15:08:04', '2021-10-20 15:08:04', NULL),
(534, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:04', '2021-10-20 15:08:04', NULL),
(535, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"122-6543215\", \"email\": \"eirhgeiorhguier@wiejfiwe.com\", \"optin\": \"on\", \"score\": \"550\", \"nombre\": \"Epifanio Guillermo\", \"telcod\": \"122\", \"telnum\": \"6543215\", \"apellido\": \"Miron\", \"direccion\": {\"nro\": \"1\", \"calle\": \"A Tczñuxirlodkqóvmxc\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-20 15:08:32', '2021-10-20 15:08:32', NULL),
(536, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:32', '2021-10-20 15:08:32', NULL),
(537, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:38', '2021-10-20 15:08:38', NULL),
(538, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"12345\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-20 15:08:41', '2021-10-20 15:08:41', NULL),
(539, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:41', '2021-10-20 15:08:41', NULL),
(540, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-20 15:08:42', '2021-10-20 15:08:42', NULL),
(541, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:42', '2021-10-20 15:08:42', NULL),
(542, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"1910867811100088871164\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-20 15:08:57', '2021-10-20 15:08:57', NULL),
(543, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:57', '2021-10-20 15:08:57', NULL),
(544, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"isTrusted\": \"1\", \"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-20 15:08:59', '2021-10-20 15:08:59', NULL),
(545, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:08:59', '2021-10-20 15:08:59', NULL),
(546, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"confirmar-por-mail\", \"paso_descripcion\": \"Se le envió un mail y el visitante tiene que escribir la aceptación (código).\"}}', '2021-10-20 15:40:15', '2021-10-20 15:40:15', NULL),
(547, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:40:15', '2021-10-20 15:40:15', NULL);
INSERT INTO `solicitudes_log` (`id`, `solicitud_id`, `tipo`, `texto`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(548, 2, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-20 15:40:40', '2021-10-20 15:40:40', NULL),
(549, 2, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-20 15:40:40', '2021-10-20 15:40:40', NULL),
(550, 0, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:37:31', '2021-10-21 07:37:31', NULL),
(551, 3, 'INFO', 'Se validaron los datos correctamente', '{\"data\": {\"plazo\": \"13\", \"origen\": \"FRONT\", \"capital\": \"27500\", \"rangeMonto\": \"27500\", \"rangePlazo\": \"13\", \"numberMonto\": \"27500\", \"numberPlazo\": \"13\"}, \"origen\": \"FRONT\", \"negocio_id\": \"1\", \"ws_usuario_id\": \"1\"}', '2021-10-21 07:37:31', '2021-10-21 07:37:31', NULL),
(552, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-21 07:37:31', '2021-10-21 07:37:31', NULL),
(553, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:37:31', '2021-10-21 07:37:31', NULL),
(554, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-21 07:37:32', '2021-10-21 07:37:32', NULL),
(555, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:37:32', '2021-10-21 07:37:32', NULL),
(556, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"plan\": \"1\", \"nro_doc\": \"98765422\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"654564DFWEEd232\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-21 07:37:41', '2021-10-21 07:37:41', NULL),
(557, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:37:41', '2021-10-21 07:37:41', NULL),
(558, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"tel\": \"123-4567890\", \"email\": \"kwjeoifwjefoijiowe@wkejcoiwe.com\", \"optin\": \"on\", \"score\": \"420\", \"nombre\": \"Dafne Grisel\", \"telcod\": \"123\", \"telnum\": \"4567890\", \"apellido\": \"Quispe\", \"direccion\": {\"nro\": \"153\", \"calle\": \"Carriego\"}, \"paso_alias\": \"telefono-mail\", \"paso_descripcion\": \"El visitante debe ingresar su número de teléfono y su dirección de correo electrónico.\"}}', '2021-10-21 07:37:58', '2021-10-21 07:37:58', NULL),
(559, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:37:58', '2021-10-21 07:37:58', NULL),
(560, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:38:09', '2021-10-21 07:38:09', NULL),
(561, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"PIN\": \"14535\", \"paso_alias\": \"codigo-pin\", \"paso_descripcion\": \"Donde el visitante ingresa el PIN enviado a su celular\"}}', '2021-10-21 07:38:14', '2021-10-21 07:38:14', NULL),
(562, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:38:14', '2021-10-21 07:38:14', NULL),
(563, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"identidad-verificada\", \"paso_descripcion\": \"El PIN es correcto\"}}', '2021-10-21 07:38:20', '2021-10-21 07:38:20', NULL),
(564, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:38:20', '2021-10-21 07:38:20', NULL),
(565, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"cbu\": \"0201227211100004413364\", \"checkDeb\": \"on\", \"paso_alias\": \"ingresar-cbu\", \"paso_descripcion\": \"Donde el visitante debe ingresar su CBU\"}}', '2021-10-21 07:38:38', '2021-10-21 07:38:38', NULL),
(566, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:38:38', '2021-10-21 07:38:38', NULL),
(567, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"isTrusted\": \"1\", \"paso_alias\": \"resumen-prestamo\", \"paso_descripcion\": \"El visitante confirma que es éste el préstamo que quiere que se le otorgue.\"}}', '2021-10-21 07:38:42', '2021-10-21 07:38:42', NULL),
(568, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 07:38:42', '2021-10-21 07:38:42', NULL),
(569, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"confirmar-por-mail\", \"paso_descripcion\": \"Se le envió un mail y el visitante tiene que escribir la aceptación (código).\", \"codigo_aceptacion\": \"10mcg\"}}', '2021-10-21 08:20:39', '2021-10-21 08:20:39', NULL),
(570, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 08:20:39', '2021-10-21 08:20:39', NULL),
(571, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"solicitalo-ahora\", \"paso_descripcion\": \"Calculadora de préstamo que se muestra en el inicio.\"}}', '2021-10-21 08:24:41', '2021-10-21 08:24:41', NULL),
(572, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 08:24:41', '2021-10-21 08:24:41', NULL),
(573, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"ingresa-cuenta\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}}', '2021-10-21 08:24:42', '2021-10-21 08:24:42', NULL),
(574, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 08:24:42', '2021-10-21 08:24:42', NULL),
(575, 3, 'DEBUG', 'Solicitud modificada', '{\"data\": {\"paso_alias\": \"crear-cuenta\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}}', '2021-10-21 08:26:01', '2021-10-21 08:26:01', NULL),
(576, 3, 'INFO', 'Se validaron los datos correctamente', NULL, '2021-10-21 08:26:01', '2021-10-21 08:26:01', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de usuarios de la API';

--
-- Volcado de datos para la tabla `ws_usuarios`
--

INSERT INTO `ws_usuarios` (`id`, `negocio_id`, `username`, `password`, `config`, `modo`, `bodyencodingkey`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'mechawsuser', 'mj5raft3sl2', '{}', 'TEST', 'cec23ae6bf7b02dbc2b993e24397fedd414e04a2', '2021-08-29 09:07:02', '2021-08-29 09:07:02', 1),
(2, 1, 'DriverOp', 'estaeslapassword', NULL, 'TEST', NULL, '2021-10-12 07:29:53', '2021-10-12 07:29:53', 1);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
