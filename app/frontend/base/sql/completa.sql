-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 04-06-2021 a las 16:37:24
-- Versión del servidor: 8.0.22
-- Versión de PHP: 7.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `db_rebritgen` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `db_rebritgen`;
--
-- Base de datos: `contabilidb`
--

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
  `metadata` text NOT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `parametros` int NOT NULL DEFAULT '0',
  `en_menu` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int NOT NULL DEFAULT '999',
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `esta_protegido` tinyint(1) NOT NULL DEFAULT '0',
  `perfiles` set('ADMIN','OPER') DEFAULT NULL COMMENT 'En qué perfiles de usuario aparece este contenido',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `last_modif` datetime DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Describe de qué se trata este contenido',
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `backend_contenidos`
--

INSERT INTO `backend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `perfiles`, `estado`, `last_modif`, `descripcion`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"vista\":\"404\"}', 0, 0, 0, 1, 0, 0, '', 'HAB', NULL, 'Error 404 - Contenido no encontrado.'),
(2, 'login', 'Iniciar sesión', 'login', '{\r\n\"template\":\"login\",\r\n\"vista\":\"login\",\r\n\"css\":\"formularios\",\r\n\"js\":\"\",\r\n\"hasmainmenu\":false,\r\n\"hassubmenu\":false,\r\n\"hascarrusel\":false\r\n}', 0, 0, 0, 2, 0, 0, '', 'HAB', NULL, 'Pantalla de logueo o ingreso al sistema.'),
(3, 'logout', 'Cerrar sesión', 'logout', '', 0, 0, 0, 3, 0, 0, '', 'HAB', NULL, 'Cierra sesión del usuario.'),
(4, 'inicio', 'Dashboard inicial', 'pagina', '{\r\n\"vista\":\"inicio\",\r\n\"css\":\"daterangepicker,inicio\",\r\n\"js\":\"moment.min,moment_locale_es,daterangepicker,checkforms,inicio\",\r\n\"titulo\":\"Dashboard inicial\",\r\n\"menutag\":\"Diario\",\r\n\"icon_class\":\"fa-power-off\",\r\n\"description\":\"Dahsboard inicial\",\r\n\"tooltip\":\"Dashboard inicial\"\r\n}', 0, 0, 1, 4, 0, 1, 'ADMIN,OPER', 'HAB', NULL, 'Dashboard inicial'),
(5, 'cuentas', 'Cuentas', '', '{\r\n\"icon_class\":\"fa-file-invoice-dollar\",\r\n\"tooltip\":\"Administración de cuentas\"\r\n}', 0, 0, 1, 5, 0, 1, 'ADMIN,OPER', 'HAB', NULL, 'Raíz de la administración de cuentas.'),
(6, 'listado', 'Listado de cuentas', 'pagina', '{\r\n\"vista\":\"cuentas/listado_cuentas\",\r\n\"js\":\"bootstrap-select,listados,modalBsLte\",\r\n\"css\":\"bootstrap-select\",\r\n\"menutag\":\"Listado\",\r\n\"titulo\":\"Listado de cuentas\",\r\n\"icon_class\":\"fa-tools\",\r\n\"tooltip\":\"Listado de cuentas\"\r\n}', 5, 0, 1, 6, 1, 1, NULL, 'HAB', '2020-04-01 16:43:32', 'Listado de las cuentas del libro diario.'),
(7, 'grupos', 'Listado de grupos', 'pagina', '{\r\n\"vista\":\"cuentas/listado_grupos\",\r\n\"js\":\"bootstrap-select\",\r\n\"css\":\"bootstrap-select\",\r\n\"menutag\":\"Grupos\",\r\n\"titulo\":\"Listado de grupos de cuentas\",\r\n\"icon_class\":\"fa-layer-group\",\r\n\"tooltip\":\"Grupos de cuentas\"\r\n}', 5, 0, 1, 6, 1, 1, NULL, 'HAB', '2020-04-01 16:43:32', 'Listado de los grupos de cuentas del libro diario.'),
(8, 'miformulario', 'Mi Formulario', 'pagina', '{\r\n\"vista\":\"formularios/miformulario\",\r\n\"css\":\"formularios\",\r\n\"js\":\"checkforms\",\r\n\"icon_class\":\"fas fa-poll-h\",\r\n\"tooltip\":\"Ejemplo de formulario\"\r\n\r\n}', 0, 0, 1, 7, 1, 1, NULL, 'HAB', '2020-04-07 11:03:06', 'Este es el formulario que hace cosas maravillosas.'),
(9, 'contenido', 'Contenido', '', '{\"icon_class\":\"fa-book\",\"tooltip\":\"Administración de contenido\"}', 0, 0, 1, 8, 0, 1, 'ADMIN,OPER', 'HAB', NULL, 'Raíz de administración de contenidos'),
(10, 'listacontenidos', 'Lista de Contenido', 'pagina', '{\"vista\":\"contenidos/listado_contenidos\",\"js\":\"bootstrap-select\",\"css\":\"bootstrap-select\",\"menutag\":\"Listado Contenido\",\"titulo\":\"Listado de contenido\",\"icon_class\":\" fa-list-alt\",\"tooltip\":\"Listado de contenido\"}', 9, 0, 1, 9, 1, 1, NULL, 'HAB', NULL, 'Listado de contenidos'),
(11, 'ejemplo', 'Ejemplo de Contenido', 'pagina', '{\r\n\"vista\":\"ejemplo\",\r\n\"js\":\"bootstrap-select\",\r\n\"css\":\"bootstrap-select\",\r\n\"menutag\":\"Ejemplo\",\r\n\"icon_class\":\"fa-camera\",\r\n\"description\":\"Ejemplo de contenido\",\r\n\"tooltip\":\"Ejemplo de contenidos\"\r\n}', 0, 0, 1, 10, 0, 1, 'ADMIN,OPER', 'HAB', NULL, 'Ejemplo de contenidos para idiomas.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_messages`
--

DROP TABLE IF EXISTS `backend_messages`;
CREATE TABLE IF NOT EXISTS `backend_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fechahora` datetime DEFAULT NULL,
  `from_id` int DEFAULT NULL,
  `to_id` int DEFAULT NULL,
  `grupo` set('USR','ADM','OWN','OPER','ADMIN','ALL') DEFAULT 'USR',
  `texto` text,
  `estado` int DEFAULT NULL,
  `chain` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `from_id` (`from_id`),
  KEY `to_id` (`to_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_messages_read`
--

DROP TABLE IF EXISTS `backend_messages_read`;
CREATE TABLE IF NOT EXISTS `backend_messages_read` (
  `id` int NOT NULL AUTO_INCREMENT,
  `msgid` int DEFAULT NULL,
  `fechahora` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `msgid` (`msgid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_perfiles`
--

DROP TABLE IF EXISTS `backend_perfiles`;
CREATE TABLE IF NOT EXISTS `backend_perfiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alias` varchar(6) NOT NULL,
  `nombre` varchar(64) DEFAULT NULL,
  `data` text,
  `usuario_id` int DEFAULT NULL,
  `fechahora` datetime DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_perfiles_contenidos`
--

DROP TABLE IF EXISTS `backend_perfiles_contenidos`;
CREATE TABLE IF NOT EXISTS `backend_perfiles_contenidos` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `perfil_id` int DEFAULT NULL COMMENT 'ID del perfil',
  `contenido_id` int DEFAULT NULL COMMENT 'ID del contenido',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `fechahora` datetime DEFAULT NULL COMMENT 'Fecha y hora del alta',
  `usuario_id` int DEFAULT NULL COMMENT 'Usuario que dio de alta',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Los contenidos que se cargar para cada perfil';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_permisosxusuarios`
--

DROP TABLE IF EXISTS `backend_permisosxusuarios`;
CREATE TABLE IF NOT EXISTS `backend_permisosxusuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `contenido_id` int NOT NULL,
  `fechahora` datetime NOT NULL,
  `usralta_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_recovery`
--

DROP TABLE IF EXISTS `backend_recovery`;
CREATE TABLE IF NOT EXISTS `backend_recovery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(32) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_sesiones`
--

DROP TABLE IF EXISTS `backend_sesiones`;
CREATE TABLE IF NOT EXISTS `backend_sesiones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `estado` int NOT NULL DEFAULT '0',
  `navegador` varchar(255) NOT NULL,
  `idle` int NOT NULL,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha_hora` (`fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backend_usuarios`
--

DROP TABLE IF EXISTS `backend_usuarios`;
CREATE TABLE IF NOT EXISTS `backend_usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `negocio_id` int DEFAULT NULL,
  `marca_id` int DEFAULT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `iniciales` varchar(5) DEFAULT NULL,
  `password` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(75) DEFAULT NULL,
  `nivel` enum('USR','ADM','OWN') NOT NULL DEFAULT 'USR',
  `perfil_id` int DEFAULT NULL COMMENT 'Se refier al perfil de usuario',
  `opciones` text,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sucursal_id` int DEFAULT NULL,
  `tel` varchar(25) DEFAULT NULL COMMENT 'Nro de teléfono para usar en la rec. de contraseña.	',
  `fecha_alta` datetime DEFAULT NULL,
  `fecha_modif` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  `cleartext` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `backend_usuarios`
--

INSERT INTO `backend_usuarios` (`id`, `negocio_id`, `marca_id`, `nombre`, `apellido`, `username`, `iniciales`, `password`, `email`, `nivel`, `perfil_id`, `opciones`, `estado`, `sucursal_id`, `tel`, `fecha_alta`, `fecha_modif`, `usuario_id`, `cleartext`) VALUES
(1, NULL, NULL, 'Diego', 'Romero', 'driverop', 'DFR', '$2y$10$uhe0O8MkVMOVINj2mfEnpu6De0bE/TafScaB7BgLD13AoLPVZWKEK', 'diego.romero@driverop.com', 'OWN', NULL, '{\r\n\"rpp\":\"25\",\r\n\"tsession\":3600\r\n}', 'HAB', NULL, '3446-623494', '2020-04-01 10:55:00', '2021-05-28 15:34:05', 1, NULL);

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
  `tipo` enum('INT','FLOAT','STRING','BOOL','MONEDA') DEFAULT 'STRING' COMMENT 'Tipo de dato permitido para la variable	',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `permiso` enum('ADMIN','OPER') DEFAULT NULL COMMENT 'Indica qué rol debe tener el usuario que puede editar este valor',
  `descripcion` text COMMENT 'Descripción del uso de la variable',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de la última modificación',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de alta.',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'ID del usuario que dio el alta o modificó el registro.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `config_parametros`
--

INSERT INTO `config_parametros` (`id`, `grupo_id`, `nombre`, `valor`, `tipo`, `estado`, `permiso`, `descripcion`, `sys_fecha_modif`, `sys_fecha_alta`, `sys_usuario_id`) VALUES
(1, 1, 'pais_origen', 'COL', 'STRING', 'HAB', 'ADMIN', 'Pais de origen del la tienda', '2021-03-16 11:27:28', '2021-03-16 11:27:28', 1);

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
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de alta.',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario que realizó la última modificación',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `config_parametros_grupos`
--

INSERT INTO `config_parametros_grupos` (`id`, `nombre`, `estado`, `descripcion`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'Sistema local', 'HAB', 'Parámetros del sistema local.', '2021-03-15 00:00:00', '2021-03-15 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cont_asientos_diarios`
--

DROP TABLE IF EXISTS `cont_asientos_diarios`;
CREATE TABLE IF NOT EXISTS `cont_asientos_diarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fechahora` datetime DEFAULT NULL,
  `cuenta_id` int NOT NULL DEFAULT '0',
  `debe` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `haber` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `saldo` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `comentario` text,
  `fechahora_alta` datetime DEFAULT NULL,
  `fechahora_modif` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fechahora` (`fechahora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cont_cuentas`
--

DROP TABLE IF EXISTS `cont_cuentas`;
CREATE TABLE IF NOT EXISTS `cont_cuentas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_id` int DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `operacion` enum('ADD','SUB','NUL') DEFAULT 'ADD',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `data` text,
  `fechahora_alta` datetime DEFAULT NULL,
  `fechahora_modif` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_id` (`grupo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `cont_cuentas`
--

INSERT INTO `cont_cuentas` (`id`, `grupo_id`, `nombre`, `operacion`, `estado`, `data`, `fechahora_alta`, `fechahora_modif`, `usuario_id`) VALUES
(1, 1, 'Almacén de la esquina', 'SUB', 'HAB', NULL, '2020-04-01 17:25:07', '2020-04-01 17:25:07', 1),
(2, 1, 'Compras En La Ferretería', 'SUB', 'HAB', NULL, '2020-09-14 07:56:28', '2020-09-14 07:56:28', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cont_cuentas_grupos`
--

DROP TABLE IF EXISTS `cont_cuentas_grupos`;
CREATE TABLE IF NOT EXISTS `cont_cuentas_grupos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `data` text,
  `fechahora_alta` datetime DEFAULT NULL,
  `fechahora_modif` datetime DEFAULT NULL,
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `cont_cuentas_grupos`
--

INSERT INTO `cont_cuentas_grupos` (`id`, `nombre`, `estado`, `data`, `fechahora_alta`, `fechahora_modif`, `usuario_id`) VALUES
(1, 'Compras de almacén', 'HAB', NULL, '2020-04-01 17:24:41', '2020-04-01 17:24:41', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sqlupdate`
--

DROP TABLE IF EXISTS `sqlupdate`;
CREATE TABLE IF NOT EXISTS `sqlupdate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(256) DEFAULT NULL,
  `ronda` int DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `sqlupdate`
--

INSERT INTO `sqlupdate` (`id`, `nombre`, `ronda`, `sys_fecha_alta`) VALUES
(1, '2020-11-02_14-20-Create_Table_sys_ticket', 1, '2021-02-18 17:18:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_ticket`
--

DROP TABLE IF EXISTS `sys_ticket`;
CREATE TABLE IF NOT EXISTS `sys_ticket` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `mensaje` text,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
