-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 27-10-2021 a las 15:31:35
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
-- Base de datos: `mecha_backend`
--
CREATE DATABASE IF NOT EXISTS `mecha_backend` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mecha_backend`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_contenidos`
--

DROP TABLE IF EXISTS `backend_contenidos`;
CREATE TABLE IF NOT EXISTS `backend_contenidos` (
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
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Describe de qué se trata este contenido',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8 COMMENT='Contenidos del backend';

--
-- Truncar tablas antes de insertar `backend_contenidos`
--

TRUNCATE TABLE `backend_contenidos`;
--
-- Volcado de datos para la tabla `backend_contenidos`
--

INSERT INTO `backend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `description`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1),
(2, 'inicio', 'Dashboard', 'pagina', '{\"js\": \"inicio\", \"css\": \"inicio\", \"vista\": \"inicio\", \"menutag\": \"Dashboard\"}', 0, 0, 1, 1, 0, 1, 0, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1),
(3, 'login', 'Iniciar sesión', 'login', '{\"js\": \"login\", \"css\": \"login\", \"vista\": \"login\", \"template\": \"login\"}', 0, 0, 0, 2, 0, 0, 0, '', 'HAB', NULL, 'Pantalla de logueo o ingreso al sistema.', NULL, NULL, NULL),
(4, 'logout', 'Cerrar sesión', 'logout', '{}', 0, 0, 0, 3, 0, 0, 0, '', 'HAB', NULL, 'Cierra sesión del usuario.', NULL, NULL, NULL),
(20, 'solicitudes', 'Solicitudes', '', '{\"menutag\": \"Solicitudes\", \"icon_class\": \"fab fa-slideshare\"}', 0, 0, 1, 20, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(21, 'listado', 'Listado de solicitudes', 'pagina', '{\"js\": \"objListados.2.0,solicitudes/listado\", \"css\": \"login\", \"vista\": \"solicitudes/listado\", \"menutag\": \"Listado\", \"icon_class\": \"fad fa-list-alt\"}', 20, 0, 1, 21, 1, 1, 1, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(25, 'planes', 'Planes', '', '{\"menutag\": \"Planes\", \"icon_class\": \"fas fa-file-powerpoint\"}', 0, 0, 1, 25, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(26, 'listado', 'Lista Planes', 'pagina', '{\"js\": \"planes\", \"css\": \"planes\", \"vista\": \"planes/main\"}', 25, 0, 1, 26, 1, 1, 0, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(27, 'formulario-plan', 'Formulario plan', 'pagina', '{\"js\": \"planes_form\", \"css\": \"planes_form\", \"vista\": \"planes/newPlanes\", \"menutag\": \"Formulario Planes\"}', 25, 1, 1, 27, 0, 1, 0, NULL, 'HAB', NULL, 'Formulario de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(30, 'usuarios', 'Usuarios', '', '{\"menutag\": \"Usuarios\", \"icon_class\": \"fas fa-users\"}', 0, 0, 1, 10, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(31, 'listado', 'Lista usuarios', 'pagina', '{\"js\": \"usuarios/usuarios\", \"css\": \"usuarios/usuarios\", \"vista\": \"usuarios/main\"}', 30, 0, 0, 0, 1, 1, 0, NULL, 'HAB', NULL, 'Listado de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(90, 'tests', 'Test varios', '', '{\"menutag\": \"Tests\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 0, 999, 0, 0, 1, NULL, 'HAB', '2021-09-23 07:53:00', 'Raiz para agrupar las diferentes pruebas.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', 1),
(91, 'testmodalbs5', 'Modal para Bootstrap 5', 'pagina', '{\"js\": \"modalBs5,tests/modalbs5\", \"vista\": \"tests/modalbs5\"}', 90, 0, 0, 999, 0, 1, 0, NULL, 'HAB', '2021-09-23 07:53:00', 'Prueba de desarrolo del modal para Bootstrap 5.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', NULL),
(100, 'configuracion', 'Configuración', '', '{\"menutag\": \"Configuración\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 1, 100, 0, 1, 0, NULL, 'HAB', NULL, 'Configuraciones del sistema', '2021-10-25 08:30:30', '2021-10-25 08:30:30', 1),
(102, 'parametros', 'Parámetros', 'pagina', '{\"js\": \"objListados.2.0,configuraciones/parametros\", \"css\": \"configuraciones/parametros\", \"vista\": \"configuraciones/parametros\", \"menutag\": \"Parámetros generales\", \"icon_class\": \"fas fa-tools\"}', 100, 0, 1, 1, 1, 1, 1, NULL, 'HAB', NULL, 'Ajustar los parámetros generales del sistema.', '2021-10-25 08:30:30', '2021-10-25 08:30:30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_personas`
--

DROP TABLE IF EXISTS `backend_personas`;
CREATE TABLE IF NOT EXISTS `backend_personas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `negocio_id` int DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `tipo_doc` enum('DNI','CUIL') DEFAULT NULL,
  `nro_doc` int DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `region` json DEFAULT NULL,
  `telefonos` json DEFAULT NULL,
  `emails` json DEFAULT NULL,
  `direcciones` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `backend_personas`
--

TRUNCATE TABLE `backend_personas`;
--
-- Volcado de datos para la tabla `backend_personas`
--

INSERT INTO `backend_personas` (`id`, `negocio_id`, `nombre`, `apellido`, `tipo_doc`, `nro_doc`, `fecha_nac`, `region`, `telefonos`, `emails`, `direcciones`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'Diego Francisco', 'Romero Maglione', 'DNI', 12345678, '1973-04-28', NULL, NULL, NULL, NULL, '2021-09-10 00:00:00', '2021-09-10 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_personas_data`
--

DROP TABLE IF EXISTS `backend_personas_data`;
CREATE TABLE IF NOT EXISTS `backend_personas_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int NOT NULL,
  `tipo` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'EMAIL' COMMENT 'Tipo de dato. EMAIL, TEL, CBU etc',
  `dato` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'HAB',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si este datos es el dato principal o por omisión',
  `extras` json DEFAULT NULL COMMENT 'Cualquier otra información relativa al dato.',
  `duplicado` int DEFAULT NULL COMMENT 'Indica si es un dato que pertenece a otro cliente(solo si fue agregado desde el backend)',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fehca y hora del alta',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario que hizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  KEY `tipo` (`tipo`,`dato`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Datos extras de las personas';

--
-- Truncar tablas antes de insertar `backend_personas_data`
--

TRUNCATE TABLE `backend_personas_data`;
--
-- Volcado de datos para la tabla `backend_personas_data`
--

INSERT INTO `backend_personas_data` (`id`, `persona_id`, `tipo`, `dato`, `estado`, `default`, `extras`, `duplicado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'EMAIL', 'diego.romero@rebrit.ar', 'HAB', 1, '{\"razon\": \"Dirección aún no fue verificada\", \"valido\": false, \"verificado\": false}', NULL, '2021-09-21 16:09:22', '2021-09-21 16:09:22', 1),
(2, 1, 'EMAIL', 'driverop@gmail.com', 'HAB', 0, '{\"razon\": \"Dirección aún no verificada\", \"valido\": false, \"verificado\": false}', NULL, '2021-09-21 16:14:47', '2021-09-21 16:14:47', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_sessions`
--

DROP TABLE IF EXISTS `backend_sessions`;
CREATE TABLE IF NOT EXISTS `backend_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `estado` int DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL,
  `idle` int DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `backend_sessions`
--

TRUNCATE TABLE `backend_sessions`;
--
-- Volcado de datos para la tabla `backend_sessions`
--

INSERT INTO `backend_sessions` (`id`, `usuario_id`, `sys_fecha_alta`, `estado`, `navegador`, `idle`, `ip`) VALUES
(1, 1, '2021-09-21 14:15:37', 0, 'RYZEN5600X', 1632244537, 'CLImode'),
(2, 1, '2021-09-21 14:24:12', 0, 'RYZEN5600X', 1632245052, 'CLImode'),
(3, 1, '2021-09-22 08:08:54', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632308934, '::1'),
(4, 1, '2021-09-22 09:55:23', 0, 'PostmanRuntime/7.28.4', 1632315323, '127.0.0.1'),
(5, 1, '2021-09-22 09:57:22', 0, 'PostmanRuntime/7.28.4', 1632315442, '127.0.0.1'),
(6, 1, '2021-09-22 10:05:47', 0, 'PostmanRuntime/7.28.4', 1632315947, '127.0.0.1'),
(7, 1, '2021-09-22 10:06:29', 0, 'PostmanRuntime/7.28.4', 1632315989, '127.0.0.1'),
(8, 1, '2021-09-22 10:22:39', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632316959, '::1'),
(9, 1, '2021-09-22 10:24:06', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632317046, '::1'),
(10, 1, '2021-09-22 10:27:51', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632317271, '::1'),
(11, 1, '2021-09-22 10:28:54', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632317334, '::1'),
(12, 1, '2021-09-22 11:05:29', 0, 'PostmanRuntime/7.28.4', 1632319529, '127.0.0.1'),
(13, 1, '2021-09-22 11:05:34', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632319534, '::1'),
(14, 1, '2021-09-22 11:05:57', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632319557, '::1'),
(15, 1, '2021-09-22 11:09:36', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632319776, '::1'),
(16, 1, '2021-09-22 11:16:39', 0, 'RYZEN5600X', 1632320199, 'CLImode'),
(17, 1, '2021-09-22 11:17:00', 0, 'RYZEN5600X', 1632320220, 'CLImode'),
(18, 1, '2021-09-22 11:18:38', 0, 'RYZEN5600X', 1632320318, 'CLImode'),
(19, 1, '2021-09-22 11:19:06', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632323905, '::1'),
(20, 1, '2021-09-22 12:18:30', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632324333, '::1'),
(21, 1, '2021-09-22 13:35:50', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632329795, '::1'),
(22, 1, '2021-09-22 17:36:45', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632343045, '::1'),
(23, 1, '2021-09-23 06:46:11', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632390637, '::1'),
(24, 1, '2021-09-23 08:01:30', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.83 Safari/537.36', 1632399679, '::1'),
(25, 1, '2021-09-23 12:38:34', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632412947, '::1'),
(26, 1, '2021-09-23 16:29:50', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632425745, '::1'),
(27, 1, '2021-09-23 16:35:53', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427094, '::1'),
(28, 1, '2021-09-23 17:00:17', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427217, '::1'),
(29, 1, '2021-09-23 17:01:23', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427284, '::1'),
(30, 1, '2021-09-23 17:01:24', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427284, '::1'),
(31, 1, '2021-09-23 17:01:24', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427285, '::1'),
(32, 1, '2021-09-23 17:01:25', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427285, '::1'),
(33, 1, '2021-09-23 17:01:25', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427285, '::1'),
(34, 1, '2021-09-23 17:01:25', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427292, '::1'),
(35, 1, '2021-09-23 17:07:48', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427698, '::1'),
(36, 1, '2021-09-23 17:08:18', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427779, '::1'),
(37, 1, '2021-09-23 17:10:07', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427807, '::1'),
(38, 1, '2021-09-23 17:10:28', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632427828, '::1'),
(39, 1, '2021-09-24 06:48:31', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632476915, '::1'),
(40, 1, '2021-09-24 06:50:48', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632477072, '::1'),
(41, 1, '2021-09-24 06:51:12', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632477072, '::1'),
(42, 1, '2021-09-24 06:51:42', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632477108, '::1'),
(43, 1, '2021-09-24 06:51:58', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632477124, '::1'),
(44, 1, '2021-09-24 06:55:22', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632477426, '::1'),
(45, 1, '2021-09-24 07:03:35', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632483368, '::1'),
(46, 1, '2021-09-24 09:52:59', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632496904, '::1'),
(47, 1, '2021-09-24 13:37:43', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.57 Safari/537.36', 1632501536, '::1'),
(48, 1, '2021-10-20 10:11:55', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.72 Safari/537.36', 1634735521, '::1'),
(49, 1, '2021-10-26 08:26:53', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635262090, '::1'),
(50, 1, '2021-10-26 13:31:08', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635268917, '::1'),
(51, 1, '2021-10-26 16:48:27', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635277708, '::1'),
(52, 1, '2021-10-27 06:23:19', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635333417, '::1'),
(53, 1, '2021-10-27 10:09:10', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635347867, '::1'),
(54, 1, '2021-10-27 13:30:02', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635355584, '::1'),
(55, 1, '2021-10-27 15:27:40', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635359277, '::1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_usuarios`
--

DROP TABLE IF EXISTS `backend_usuarios`;
CREATE TABLE IF NOT EXISTS `backend_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nivel` enum('ADMIN','OPER') DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT NULL,
  `opciones` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `backend_usuarios`
--

TRUNCATE TABLE `backend_usuarios`;
--
-- Volcado de datos para la tabla `backend_usuarios`
--

INSERT INTO `backend_usuarios` (`id`, `persona_id`, `username`, `password`, `nivel`, `estado`, `opciones`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'driverop', '$2y$10$1VbJaoGl0pc7z9MBoYDeau1UmOvm783FVpnEP8erlmpPDKXa0Yf4i', 'ADMIN', 'HAB', '{\"rpp\": 25, \"tsession\": 3600}', '2021-09-10 00:00:00', '2021-10-26 08:15:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios`
--

DROP TABLE IF EXISTS `negocios`;
CREATE TABLE IF NOT EXISTS `negocios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `data` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Truncar tablas antes de insertar `negocios`
--

TRUNCATE TABLE `negocios`;
--
-- Volcado de datos para la tabla `negocios`
--

INSERT INTO `negocios` (`id`, `nombre`, `estado`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'rebrit', 'HAB', '{}', '2021-10-25 00:00:00', '2021-10-25 00:00:00', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
