SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `mecha_backend` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mecha_backend`;

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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COMMENT='Contenidos del backend';

INSERT INTO `backend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `description`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1),
(2, 'inicio', 'Dashboard', 'pagina', '{\"js\": \"inicio\", \"css\": \"inicio\", \"vista\": \"inicio\", \"menutag\": \"Dashboard\"}', 0, 0, 1, 1, 0, 1, 1, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1),
(3, 'login', 'Iniciar sesión', 'login', '{\"js\": \"login\", \"css\": \"login\", \"vista\": \"login\", \"template\": \"login\"}', 0, 0, 0, 2, 0, 0, 0, '', 'HAB', NULL, 'Pantalla de logueo o ingreso al sistema.', NULL, NULL, NULL),
(4, 'logout', 'Cerrar sesión', 'logout', '{}', 0, 0, 0, 3, 0, 0, 0, '', 'HAB', NULL, 'Cierra sesión del usuario.', NULL, NULL, NULL),
(20, 'solicitudes', 'Solicitudes', '', '{\"menutag\": \"Solicitudes\", \"icon_class\": \"fab fa-slideshare\"}', 0, 0, 1, 20, 0, 1, 1, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(21, 'listado', 'Listado de solicitudes', 'pagina', '{\"js\": \"objListados.2.0,solicitudes/listado\", \"css\": \"login\", \"vista\": \"solicitudes/listado\", \"menutag\": \"Listado\", \"icon_class\": \"fad fa-list-alt\"}', 20, 0, 1, 21, 1, 1, 1, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(25, 'planes', 'Planes', '', '{\"menutag\": \"Planes\", \"icon_class\": \"fas fa-file-powerpoint\"}', 0, 0, 1, 25, 0, 1, 1, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(26, 'listado', 'Lista Planes', 'pagina', '{\"js\": \"planes\", \"css\": \"planes\", \"vista\": \"planes/main\"}', 25, 0, 1, 26, 1, 1, 1, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(27, 'formulario-plan', 'Formulario plan', 'pagina', '{\"js\": \"planes_form\", \"css\": \"planes_form\", \"vista\": \"planes/newPlanes\", \"menutag\": \"Formulario Planes\"}', 25, 1, 1, 27, 0, 1, 1, NULL, 'HAB', NULL, 'Formulario de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(30, 'usuarios', 'Usuarios', '', '{\"menutag\": \"Usuarios\", \"icon_class\": \"fas fa-users\"}', 0, 0, 1, 10, 0, 1, 1, NULL, 'HAB', NULL, 'Página de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(31, 'listado', 'Lista usuarios', 'pagina', '{\"js\": \"usuarios/usuarios\", \"css\": \"usuarios/usuarios\", \"vista\": \"usuarios/main\"}', 30, 0, 0, 0, 1, 1, 1, NULL, 'HAB', NULL, 'Listado de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(90, 'tests', 'Test varios', '', '{\"menutag\": \"Tests\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 0, 999, 0, 0, 1, NULL, 'HAB', '2021-09-23 07:53:00', 'Raiz para agrupar las diferentes pruebas.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', 1),
(91, 'testmodalbs5', 'Modal para Bootstrap 5', 'pagina', '{\"js\": \"modalBs5,tests/modalbs5\", \"vista\": \"tests/modalbs5\"}', 90, 0, 0, 999, 0, 1, 1, NULL, 'HAB', '2021-09-23 07:53:00', 'Prueba de desarrolo del modal para Bootstrap 5.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', NULL),
(100, 'configuracion', 'Configuración', '', '{\"menutag\": \"Configuración\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 1, 100, 0, 1, 1, NULL, 'HAB', NULL, 'Configuraciones del sistema', '2021-10-25 08:30:30', '2021-10-25 08:30:30', 1),
(102, 'parametros', 'Parámetros', 'pagina', '{\"js\": \"objListados.2.0,configuraciones/parametros\", \"css\": \"configuraciones/parametros\", \"vista\": \"configuraciones/parametros\", \"menutag\": \"Parámetros generales\", \"icon_class\": \"fas fa-tools\"}', 100, 0, 1, 1, 1, 1, 1, NULL, 'HAB', NULL, 'Ajustar los parámetros generales del sistema.', '2021-10-25 08:30:30', '2021-10-25 08:30:30', 1),
(103, 'permisos', 'Permisos', '', '{\"menutag\": \"Permisos\", \"icon_class\": \"fas fa-user-lock\"}', 0, 0, 1, 1, 1, 1, 1, NULL, 'HAB', NULL, 'Vista raiz para establecer permisos por roles', '2021-10-28 08:30:30', '2021-10-28 08:30:30', 1),
(104, 'editar', 'Editar permisos', 'pagina', '{\"js\": \"permisos/main,permisos/rol,permisos/template,permisos/user\", \"css\": \"permisos/main\", \"vista\": \"permisos/main\"}', 103, 0, 0, 1, 1, 1, 1, NULL, 'HAB', NULL, 'Vista para editar permisos por roles', '2021-10-28 08:30:30', '2021-10-28 08:30:30', 1);

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

INSERT INTO `backend_personas` (`id`, `negocio_id`, `nombre`, `apellido`, `tipo_doc`, `nro_doc`, `fecha_nac`, `region`, `telefonos`, `emails`, `direcciones`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'Diego Francisco', 'Romero Maglione', 'DNI', 12345678, '1973-04-28', NULL, NULL, NULL, NULL, '2021-09-10 00:00:00', '2021-09-10 00:00:00', 1);

DROP TABLE IF EXISTS `backend_personas_data`;
CREATE TABLE IF NOT EXISTS `backend_personas_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int NOT NULL,
  `tipo` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'EMAIL' COMMENT 'Tipo de dato. EMAIL, TEL, CBU etc',
  `dato` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'HAB',
  `default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si este datos es el dato principal o por omisión',
  `extras` json DEFAULT NULL COMMENT 'Cualquier otra información relativa al dato.',
  `duplicado` int DEFAULT NULL COMMENT 'Indica si es un dato que pertenece a otro cliente(solo si fue agregado desde el backend)',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fehca y hora del alta',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario que hizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Datos extras de las personas';

INSERT INTO `backend_personas_data` (`id`, `persona_id`, `tipo`, `dato`, `estado`, `default`, `extras`, `duplicado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'EMAIL', '{\"email\": \"diego.romero@rebrit.ar\"}', 'HAB', 1, '{\"razon\": \"Dirección aún no fue verificada\", \"valido\": false, \"verificado\": false}', NULL, '2021-09-21 16:09:22', '2021-09-21 16:09:22', 1),
(2, 1, 'EMAIL', '{\"email\": \"driverop@gmail.com\"}', 'HAB', 0, '{\"razon\": \"Dirección aún no verificada\", \"valido\": false, \"verificado\": false}', NULL, '2021-09-21 16:14:47', '2021-09-21 16:14:47', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8;

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
(55, 1, '2021-10-27 15:27:40', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36', 1635359277, '::1'),
(56, 1, '2021-10-28 11:23:32', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635431529, '127.0.0.1'),
(57, 1, '2021-10-28 11:32:13', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635432252, '127.0.0.1'),
(58, 1, '2021-10-28 11:44:16', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635432648, '127.0.0.1'),
(59, 1, '2021-10-28 11:50:51', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635432773, '127.0.0.1'),
(60, 1, '2021-10-28 11:52:58', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635433201, '127.0.0.1'),
(61, 1, '2021-10-28 12:00:04', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635433815, '127.0.0.1'),
(62, 1, '2021-10-28 12:10:18', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635433820, '127.0.0.1'),
(63, 1, '2021-10-28 14:02:51', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635450769, '127.0.0.1'),
(64, 1, '2021-10-29 08:32:35', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635510343, '127.0.0.1'),
(65, 1, '2021-10-29 10:28:43', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635522336, '127.0.0.1'),
(66, 1, '2021-10-29 14:13:23', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.104 Safari/537.36', 1635536576, '127.0.0.1'),
(67, 1, '2021-11-01 08:25:24', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635782254, '127.0.0.1'),
(68, 1, '2021-11-01 14:39:20', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635796512, '127.0.0.1'),
(69, 1, '2021-11-02 08:35:36', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635867287, '127.0.0.1'),
(70, 1, '2021-11-02 14:33:06', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635874481, '127.0.0.1'),
(71, 1, '2021-11-02 15:59:10', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635883005, '127.0.0.1'),
(72, 1, '2021-11-03 08:43:42', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635954882, '127.0.0.1'),
(73, 1, '2021-11-03 14:07:55', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1635967787, '127.0.0.1'),
(74, 1, '2021-11-04 10:45:29', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636033619, '127.0.0.1'),
(75, 1, '2021-11-04 14:26:21', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636056451, '127.0.0.1'),
(76, 1, '2021-11-08 08:37:42', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636375068, '127.0.0.1'),
(77, 1, '2021-11-08 11:13:44', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636391508, '127.0.0.1'),
(78, 1, '2021-11-08 15:43:27', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636400644, '127.0.0.1'),
(79, 1, '2021-11-09 08:12:34', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636457716, '127.0.0.1'),
(80, 1, '2021-11-09 16:02:00', 0, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636484612, '127.0.0.1'),
(81, 1, '2021-11-09 16:03:35', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.114 Safari/537.36', 1636484620, '127.0.0.1');

DROP TABLE IF EXISTS `backend_usuarios`;
CREATE TABLE IF NOT EXISTS `backend_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int DEFAULT NULL,
  `rol_id` int DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nivel` enum('ADMIN','OPER','OWNER') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT NULL,
  `opciones` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `backend_usuarios` (`id`, `persona_id`, `rol_id`, `username`, `password`, `nivel`, `estado`, `opciones`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, NULL, 'driverop', '$2y$10$YzyahS.i6O9UZ3humhcINeQb7z4mHfFJaPwL0NzK9ofmDlp5oJNs.', 'OWNER', 'HAB', '{\"rpp\": 25, \"logout\": \"perfil\", \"tsession\": 3600}', '2021-09-10 00:00:00', '2021-10-28 11:23:26', 1);

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

INSERT INTO `negocios` (`id`, `nombre`, `estado`, `data`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'rebrit', 'HAB', '{}', '2021-10-25 00:00:00', '2021-10-25 00:00:00', 1);

DROP TABLE IF EXISTS `permisos_plantillas`;
CREATE TABLE IF NOT EXISTS `permisos_plantillas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) DEFAULT NULL,
  `plantilla` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `permisos_plantillas` (`id`, `nombre`, `plantilla`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(4, 'Todos los permisos', '{\"2\": {\"id\": \"2\", \"alias\": \"inicio\", \"nombre\": \"Dashboard\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"20\": {\"id\": \"20\", \"alias\": \"solicitudes\", \"childs\": {\"21\": {\"id\": \"21\", \"alias\": \"listado\", \"nombre\": \"Listado de solicitudes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Solicitudes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"25\": {\"id\": \"25\", \"alias\": \"planes\", \"childs\": {\"26\": {\"id\": \"26\", \"alias\": \"listado\", \"nombre\": \"Lista Planes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"27\": {\"id\": \"27\", \"alias\": \"formulario-plan\", \"nombre\": \"Formulario plan\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Planes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"30\": {\"id\": \"30\", \"alias\": \"usuarios\", \"childs\": {\"31\": {\"id\": \"31\", \"alias\": \"listado\", \"nombre\": \"Lista usuarios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Usuarios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"90\": {\"id\": \"90\", \"alias\": \"tests\", \"childs\": {\"91\": {\"id\": \"91\", \"alias\": \"testmodalbs5\", \"nombre\": \"Modal para Bootstrap 5\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Test varios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"100\": {\"id\": \"100\", \"alias\": \"configuracion\", \"childs\": {\"102\": {\"id\": \"102\", \"alias\": \"parametros\", \"nombre\": \"Parámetros\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Configuración\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"103\": {\"id\": \"103\", \"alias\": \"permisos\", \"childs\": {\"104\": {\"id\": \"104\", \"alias\": \"editar\", \"nombre\": \"Editar permisos\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Permisos\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}', 'HAB', '2021-11-08 14:11:44', '2021-11-08 14:11:44', 1);

DROP TABLE IF EXISTS `permisos_roles`;
CREATE TABLE IF NOT EXISTS `permisos_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) DEFAULT NULL,
  `plantilla` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `permisos_roles` (`id`, `nombre`, `plantilla`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(10, 'Vendedor', '{\"2\": {\"id\": \"2\", \"alias\": \"inicio\", \"nombre\": \"Dashboard\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"20\": {\"id\": \"20\", \"alias\": \"solicitudes\", \"childs\": {\"21\": {\"id\": \"21\", \"alias\": \"listado\", \"nombre\": \"Listado de solicitudes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Solicitudes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"25\": {\"id\": \"25\", \"alias\": \"planes\", \"childs\": {\"26\": {\"id\": \"26\", \"alias\": \"listado\", \"nombre\": \"Lista Planes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"27\": {\"id\": \"27\", \"alias\": \"formulario-plan\", \"nombre\": \"Formulario plan\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Planes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"30\": {\"id\": \"30\", \"alias\": \"usuarios\", \"childs\": {\"31\": {\"id\": \"31\", \"alias\": \"listado\", \"nombre\": \"Lista usuarios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Usuarios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"90\": {\"id\": \"90\", \"alias\": \"tests\", \"childs\": {\"91\": {\"id\": \"91\", \"alias\": \"testmodalbs5\", \"nombre\": \"Modal para Bootstrap 5\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Test varios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"100\": {\"id\": \"100\", \"alias\": \"configuracion\", \"childs\": {\"102\": {\"id\": \"102\", \"alias\": \"parametros\", \"nombre\": \"Parámetros\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Configuración\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"103\": {\"id\": \"103\", \"alias\": \"permisos\", \"childs\": {\"104\": {\"id\": \"104\", \"alias\": \"editar\", \"nombre\": \"Editar permisos\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Permisos\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}', 'HAB', '2021-11-08 12:40:57', '2021-11-08 16:04:29', 1);

DROP TABLE IF EXISTS `permisos_usuarios`;
CREATE TABLE IF NOT EXISTS `permisos_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `plantilla` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `permisos_usuarios` (`id`, `usuario_id`, `plantilla`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(10, 1, '{\"2\": {\"id\": \"2\", \"alias\": \"inicio\", \"nombre\": \"Dashboard\"}, \"20\": {\"id\": \"20\", \"alias\": \"solicitudes\", \"childs\": {\"21\": {\"id\": \"21\", \"alias\": \"listado\", \"nombre\": \"Listado de solicitudes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Solicitudes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"25\": {\"id\": \"25\", \"alias\": \"planes\", \"childs\": {\"26\": {\"id\": \"26\", \"alias\": \"listado\", \"nombre\": \"Lista Planes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"27\": {\"id\": \"27\", \"alias\": \"formulario-plan\", \"nombre\": \"Formulario plan\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Planes\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"30\": {\"id\": \"30\", \"alias\": \"usuarios\", \"childs\": {\"31\": {\"id\": \"31\", \"alias\": \"listado\", \"nombre\": \"Lista usuarios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Usuarios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"90\": {\"id\": \"90\", \"alias\": \"tests\", \"childs\": {\"91\": {\"id\": \"91\", \"alias\": \"testmodalbs5\", \"nombre\": \"Modal para Bootstrap 5\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Test varios\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"100\": {\"id\": \"100\", \"alias\": \"configuracion\", \"childs\": {\"102\": {\"id\": \"102\", \"alias\": \"parametros\", \"nombre\": \"Parámetros\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Configuración\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}, \"103\": {\"id\": \"103\", \"alias\": \"permisos\", \"childs\": {\"104\": {\"id\": \"104\", \"alias\": \"editar\", \"nombre\": \"Editar permisos\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}, \"nombre\": \"Permisos\", \"permisos\": {\"c\": \"c\", \"d\": \"d\", \"r\": \"r\", \"u\": \"u\"}}}', 'HAB', '2021-11-08 12:09:28', '2021-11-08 15:43:35', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
