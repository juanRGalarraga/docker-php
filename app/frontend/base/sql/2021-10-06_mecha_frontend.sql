-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 06-10-2021 a las 09:36:17
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
-- Base de datos: `mecha_frontend`
--
CREATE DATABASE IF NOT EXISTS `mecha_frontend` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mecha_frontend`;

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
-- Truncar tablas antes de insertar `frontend_contenidos`
--

TRUNCATE TABLE `frontend_contenidos`;
--
-- Volcado de datos para la tabla `frontend_contenidos`
--

INSERT INTO `frontend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `estado`, `sys_fecha_modif`, `sys_fecha_alta`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', NULL, 0, 0, 0, 1, 0, 0, 'HAB', '2021-08-29 17:39:33', '2021-08-29 17:39:33', NULL),
(2, 'inicio', 'Préstamos', 'pagina', '{\"js\": \"calculadora/objCalculadora,calculadora/calculadora,onboarding\", \"css\": \"inicio\", \"vista\": \"inicio\", \"menutag\": \"Inicio\", \"tooltip\": \"Volver al inicio del sistema\", \"keywords\": \"Ombú, Vivus, préstamos, online, tarjeta de credito, tarjeta credito, Visa, Credial, Mastercard, Diners, Naranja, Afluenta\", \"onboarding\": true, \"calculadora\": \"visible\", \"description\": \"Something cool is coming soon\"}', 0, 0, 1, 1, 0, 0, 'HAB', '2021-08-29 17:39:33', '2021-08-29 17:39:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `frontend_solicitudes`
--

DROP TABLE IF EXISTS `frontend_solicitudes`;
CREATE TABLE IF NOT EXISTS `frontend_solicitudes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `solic_id` int DEFAULT NULL COMMENT 'ID de la solicitud del core.',
  `data` json DEFAULT NULL COMMENT 'Los datos proporcionados por el visitante para la solicitud.',
  `estado` enum('INIT','ENDOK','ENDFAIL','HOLD') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'INIT' COMMENT 'El estado actual de la solicitud.',
  `alias_paso` varchar(64) DEFAULT NULL COMMENT 'El alias del último paso visitado (NO ES el control de pasos del onboarding!)',
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'IP del visitante',
  `user_agent` varchar(256) DEFAULT NULL COMMENT 'El User-Agent del navegador del visitante.',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de creación',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación al registro',
  PRIMARY KEY (`id`),
  KEY `solic_id` (`solic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Solicitudes del visitante.';

--
-- Truncar tablas antes de insertar `frontend_solicitudes`
--

TRUNCATE TABLE `frontend_solicitudes`;
--
-- Volcado de datos para la tabla `frontend_solicitudes`
--

INSERT INTO `frontend_solicitudes` (`id`, `solic_id`, `data`, `estado`, `alias_paso`, `ip`, `user_agent`, `sys_fecha_alta`, `sys_fecha_modif`) VALUES
(1, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', '2021-10-01 15:27:57', '2021-10-01 15:27:57'),
(2, 28, '{\"PIN\": 98765, \"cbu\": 1.7007564000007714e20, \"email\": \"gj3io4jg3i4j@elkrvoerijg.vo\", \"optin\": \"on\", \"telcod\": 456, \"telnum\": 9876543, \"nro_doc\": 8984654, \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"65489erokger9ig\", \"checkAuth\": \"on\", \"rangeMonto\": 25000, \"rangePlazo\": 28, \"numberMonto\": 25000, \"numberPlazo\": 28}', 'INIT', 'ingresar-cbu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', '2021-10-02 06:39:32', '2021-10-02 08:12:00'),
(3, 29, '{\"PIN\": 14535, \"cbu\": 1.438834711100086e21, \"email\": \"kurhiuehriufe@xdnifuneiufw.com\", \"optin\": \"on\", \"telcod\": 223, \"telnum\": 5551545, \"nro_doc\": 6548521, \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"Aieurhfuier8767\", \"checkAuth\": \"on\", \"rangeMonto\": 30000, \"rangePlazo\": 10, \"numberMonto\": 30000, \"numberPlazo\": 10}', 'INIT', 'ingresar-cbu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-02 10:08:53', '2021-10-02 10:35:16'),
(4, 30, '{\"cbu\": 2.625848511100086e21, \"checkDeb\": \"on\", \"rangeMonto\": 17500, \"rangePlazo\": 18, \"numberMonto\": 17500, \"numberPlazo\": 18}', 'INIT', 'ingresar-cbu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-02 18:55:54', '2021-10-02 19:15:05'),
(5, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-03 07:13:12', '2021-10-03 07:13:12'),
(6, 31, '{\"PIN\": \"14535\", \"cbu\": \"2625848511100086347302\", \"email\": \"werjhwiuefhuiwhef@dkfnviuerjigue.com\", \"optin\": \"on\", \"telcod\": \"233\", \"telnum\": \"3454733\", \"nro_doc\": \"4582212\", \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"uiweoug8787\", \"checkAuth\": \"on\", \"rangeMonto\": \"11000\", \"rangePlazo\": \"25\", \"numberMonto\": \"11000\", \"numberPlazo\": \"25\"}', 'INIT', 'ingresar-cbu', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-03 14:03:19', '2021-10-03 14:41:54'),
(7, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-04 06:35:45', '2021-10-04 06:35:45'),
(8, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-04 12:45:43', '2021-10-04 12:45:43'),
(9, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-04 12:45:43', '2021-10-04 12:45:43'),
(10, 35, '{\"PIN\": \"14535\", \"cbu\": \"3869904711100069478931\", \"email\": \"iuuwfuywgefwe@nebgyuberyg.com\", \"optin\": \"on\", \"telcod\": \"366\", \"telnum\": \"6848450\", \"nro_doc\": \"37564938\", \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"As129387487234\", \"checkAuth\": \"on\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\"}', 'INIT', 'crear-cuenta', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-04 15:17:15', '2021-10-04 15:32:50'),
(11, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36', '2021-10-04 15:35:41', '2021-10-04 15:35:41'),
(12, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0', '2021-10-04 15:36:44', '2021-10-04 15:36:44'),
(13, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0', '2021-10-04 15:41:40', '2021-10-04 15:41:40'),
(14, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0', '2021-10-04 15:53:29', '2021-10-04 15:53:29'),
(15, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0', '2021-10-04 15:57:39', '2021-10-04 15:57:39'),
(16, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:93.0) Gecko/20100101 Firefox/93.0', '2021-10-04 15:57:58', '2021-10-04 15:57:58'),
(17, 37, '{\"PIN\": \"84802\", \"cbu\": \"3869904711100069478930\", \"paso\": null, \"alias\": \"datos-personales\", \"email\": \"driverop@gmail.com\", \"optin\": \"on\", \"telcod\": \"9788\", \"telnum\": \"987542\", \"nro_doc\": \"11111111\", \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"12421342jhuhj\", \"checkAuth\": \"on\", \"paso_alias\": \"identidad-verificada\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\", \"paso_descripcion\": \"El PIN es correcto\"}', 'INIT', 'identidad-verificada', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-05 05:48:38', '2021-10-05 09:36:52'),
(18, NULL, NULL, 'INIT', 'solicitalo-ahora', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-05 13:36:47', '2021-10-05 13:36:47'),
(19, 38, '{\"paso_alias\": \"ingresa-cuenta\", \"rangeMonto\": \"17500\", \"rangePlazo\": \"18\", \"numberMonto\": \"17500\", \"numberPlazo\": \"18\", \"paso_descripcion\": \"Donde se le pide al visitante que ingrese a micuenta o bién cree una cuenta nueva\"}', 'INIT', 'ingresa-cuenta', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-05 13:36:47', '2021-10-05 15:08:06'),
(20, 39, '{\"PIN\": \"14535\", \"cbu\": \"3869904711100069478930\", \"email\": \"\", \"optin\": null, \"telcod\": \"\", \"telnum\": \"\", \"nro_doc\": \"8998465\", \"checkDeb\": \"on\", \"checkPdP\": \"on\", \"checkTyc\": \"on\", \"password\": \"6549845g45g4\", \"checkAuth\": \"on\", \"paso_alias\": \"crear-cuenta\", \"rangeMonto\": \"22000\", \"rangePlazo\": \"25\", \"numberMonto\": \"22000\", \"numberPlazo\": \"25\", \"paso_descripcion\": \"Se pide DNI y contraseña para crear la cuenta\"}', 'INIT', 'crear-cuenta', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', '2021-10-06 08:36:12', '2021-10-06 09:06:53');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
