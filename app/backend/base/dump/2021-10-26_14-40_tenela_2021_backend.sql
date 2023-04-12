-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 26-10-2021 a las 17:39:39
-- Versión del servidor: 8.0.18
-- Versión de PHP: 7.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tenela_2021_backend`
--
CREATE DATABASE IF NOT EXISTS `tenela_2021_backend` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tenela_2021_backend`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_contenidos`
--

DROP TABLE IF EXISTS `backend_contenidos`;
CREATE TABLE IF NOT EXISTS `backend_contenidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `controlador` varchar(50) NOT NULL,
  `metadata` json NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `parametros` int(11) NOT NULL DEFAULT '0',
  `en_menu` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int(11) NOT NULL DEFAULT '999',
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `esta_protegido` tinyint(1) NOT NULL DEFAULT '0',
  `permit` tinyint(1) DEFAULT '0' COMMENT 'Indica si el contenido entra en la zona de permisos',
  `perfiles` set('ADMIN','OPER') DEFAULT NULL COMMENT 'En qué perfiles de usuario aparece este contenido',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `last_modif` datetime DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Describe de qué se trata este contenido',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COMMENT='Contenidos del backend';

--
-- Volcado de datos para la tabla `backend_contenidos`
--

INSERT INTO `backend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `description`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1),
(2, 'inicio', 'Inicio', 'pagina', '{\"js\": \"inicio\", \"css\": \"inicio\", \"vista\": \"inicio\", \"icon_class\": \"fas fa-house-user\"}', 0, 0, 1, 0, 0, 1, 0, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1),
(3, 'login', 'Iniciar sesión', 'login', '{\"js\": \"login\", \"css\": \"login\", \"vista\": \"login\", \"template\": \"login\"}', 0, 0, 0, 2, 0, 0, 0, '', 'HAB', NULL, 'Pantalla de logueo o ingreso al sistema.', NULL, NULL, NULL),
(4, 'logout', 'Cerrar sesión', 'logout', '{}', 0, 0, 0, 3, 0, 0, 0, '', 'HAB', NULL, 'Cierra sesión del usuario.', NULL, NULL, NULL),
(20, 'solicitudes', 'Solicitudes', '', '{\"menutag\": \"Solicitudes\", \"icon_class\": \"fab fa-slideshare\"}', 0, 0, 1, 1, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(21, 'listado', 'Listado Solicitudes', 'pagina', '{\"js\": \"solicitudes/solicitudes\", \"css\": \"solicitudes\", \"vista\": \"solicitudes/main\"}', 20, 0, 0, 0, 1, 1, 0, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-24 18:40:23', '2021-09-24 18:40:23', 1),
(25, 'planes', 'Planes', '', '{\"menutag\": \"Planes\", \"icon_class\": \"fas fa-file-powerpoint\"}', 0, 0, 1, 2, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(26, 'listado', 'Lista Planes', 'pagina', '{\"js\": \"planes/planes\", \"css\": \"planes/planes\", \"vista\": \"planes/main\"}', 25, 0, 0, 0, 1, 1, 0, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(27, 'formulario-plan', 'Formulario plan', 'pagina', '{\"js\": \"planes_form\", \"css\": \"planes_form\", \"vista\": \"planes/newPlanes\", \"menutag\": \"Formulario Planes\"}', 25, 1, 0, 0, 0, 1, 0, NULL, 'HAB', NULL, 'Formulario de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(30, 'usuarios', 'Usuarios', '', '{\"menutag\": \"Usuarios\", \"icon_class\": \"fas fa-users\"}', 0, 0, 1, 10, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(31, 'listado', 'Lista usuarios', 'pagina', '{\"js\": \"usuarios/usuarios\", \"css\": \"usuarios/usuarios\", \"vista\": \"usuarios/main\"}', 30, 0, 0, 0, 1, 1, 0, NULL, 'HAB', NULL, 'Listado de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(90, 'tests', 'Test varios', '', '{\"menutag\": \"Tests\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 0, 999, 0, 0, 1, NULL, 'HAB', '2021-09-23 07:53:00', 'Raiz para agrupar las diferentes pruebas.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', 1),
(91, 'testmodalbs5', 'Modal para Bootstrap 5', 'pagina', '{\"js\": \"modalBs5,tests/modalbs5\", \"vista\": \"tests/modalbs5\"}', 90, 0, 0, 999, 0, 1, 0, NULL, 'HAB', '2021-09-23 07:53:00', 'Prueba de desarrolo del modal para Bootstrap 5.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_personas`
--

DROP TABLE IF EXISTS `backend_personas`;
CREATE TABLE IF NOT EXISTS `backend_personas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellido` varchar(255) DEFAULT NULL,
  `tipo_doc` enum('DNI','CUIL') DEFAULT NULL,
  `nro_doc` int(11) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `region` json DEFAULT NULL,
  `telefonos` json DEFAULT NULL,
  `emails` json DEFAULT NULL,
  `direcciones` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) NOT NULL,
  `tipo` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'EMAIL' COMMENT 'Tipo de dato. EMAIL, TEL, CBU etc',
  `dato` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'HAB',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si este datos es el dato principal o por omisión',
  `extras` json DEFAULT NULL COMMENT 'Cualquier otra información relativa al dato.',
  `duplicado` int(11) DEFAULT NULL COMMENT 'Indica si es un dato que pertenece a otro cliente(solo si fue agregado desde el backend)',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fehca y hora del alta',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación',
  `sys_usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que hizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  KEY `tipo` (`tipo`,`dato`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Datos extras de las personas';

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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL,
  `idle` int(11) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `backend_sessions`
--

INSERT INTO `backend_sessions` (`id`, `usuario_id`, `sys_fecha_alta`, `estado`, `navegador`, `idle`, `ip`) VALUES
(38, 1, '2021-10-25 09:13:07', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36 Edg/95.0.1020.30', 1635177087, '127.0.0.1'),
(39, 1, '2021-10-25 14:59:43', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36 Edg/95.0.1020.30', 1635190577, '127.0.0.1'),
(40, 1, '2021-10-26 11:36:37', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36 Edg/95.0.1020.30', 1635259185, '127.0.0.1'),
(41, 1, '2021-10-26 11:40:03', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36 Edg/95.0.1020.30', 1635262381, '127.0.0.1'),
(42, 1, '2021-10-26 14:37:14', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.54 Safari/537.36 Edg/95.0.1020.30', 1635269836, '127.0.0.1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_usuarios`
--

DROP TABLE IF EXISTS `backend_usuarios`;
CREATE TABLE IF NOT EXISTS `backend_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nivel` enum('ADMIN','OPER') DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT NULL,
  `opciones` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `backend_usuarios`
--

INSERT INTO `backend_usuarios` (`id`, `persona_id`, `username`, `password`, `nivel`, `estado`, `opciones`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'driverop', '$2y$10$rqSaICotRd46ounspJbG2.SOxFlvgpCn6fBc6CcNT/LpZ4.c4UB52', 'ADMIN', 'HAB', '{\"rpp\": 25, \"logout\": \"perfil\", \"tsession\": 3600}', '2021-09-10 00:00:00', '2021-09-23 15:34:35', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `negocios`
--

DROP TABLE IF EXISTS `negocios`;
CREATE TABLE IF NOT EXISTS `negocios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `data` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `negocios`
--

INSERT INTO `negocios` (`id`, `nombre`, `estado`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'rebrit', 'HAB', '{}', '2021-10-25 00:00:00', '2021-10-25 00:00:00', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
