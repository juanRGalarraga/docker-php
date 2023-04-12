-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 18-11-2021 a las 18:36:45
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
-- Base de datos: `mecha_core`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_mesa_indicencias`
--

DROP TABLE IF EXISTS `mensajes_mesa_indicencias`;
CREATE TABLE IF NOT EXISTS `mensajes_mesa_indicencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incidencia_id` int(11) DEFAULT NULL,
  `desde` enum('P','U','E','A') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'P = "Persona" , U = "Usuario" , E = "Externo" , "A" = "Automatica"',
  `mensaje` text,
  `visto` enum('SI','NO') NOT NULL DEFAULT 'NO',
  `files` json DEFAULT NULL,
  `data_usuario` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT CURRENT_TIMESTAMP,
  `sys_fecha_modif` datetime DEFAULT CURRENT_TIMESTAMP,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `mensajes_mesa_indicencias`
--

INSERT INTO `mensajes_mesa_indicencias` (`id`, `incidencia_id`, `desde`, `mensaje`, `visto`, `files`, `data_usuario`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'P', 'Hola , Esta Disponible ? ', 'NO', NULL, NULL, 'HAB', '2021-11-15 15:46:03', '2021-11-15 15:46:03', NULL),
(14, 1, 'U', 'Hola , Sisi', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:09:20', '2021-11-16 16:09:20', NULL),
(15, 1, 'U', 'Te dejo el archivo', 'NO', '[\"2021-11-16_16-09-30Ficha_de_convocatoria_TSI_441-2021.pdf\"]', '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'ELI', '2021-11-16 16:09:30', '2021-11-17 09:29:59', NULL),
(16, 1, 'U', '31', 'NO', '[\"2021-11-16_16-11-15Propuesta de proyecto HC (1).pdf\"]', '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:11:15', '2021-11-16 16:11:15', NULL),
(17, 1, 'U', 'Anashe', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:48:15', '2021-11-16 16:48:15', NULL),
(18, 1, 'U', '421412', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:48:52', '2021-11-16 16:48:52', NULL),
(19, 1, 'U', '412412', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:49:07', '2021-11-16 16:49:07', NULL),
(20, 1, 'U', '412412', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:50:28', '2021-11-16 16:50:28', NULL),
(21, 1, 'U', '412412415235', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-16 16:50:35', '2021-11-16 16:50:35', NULL),
(22, 1, 'U', '124214', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'ELI', '2021-11-16 16:50:58', '2021-11-17 09:29:46', NULL),
(23, 1, 'U', 'Documento', 'NO', '[\"2021-11-16_16-52-46Propuesta de proyecto HC (1).pdf\"]', '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'ELI', '2021-11-16 16:52:46', '2021-11-17 09:27:52', NULL),
(25, 3, 'P', 'Hi Crack 2', 'NO', NULL, '{\"nombre_usuario\": null}', 'HAB', '2021-11-17 11:06:06', '2021-11-17 11:06:06', NULL),
(26, 3, 'A', 'Hola, esto es una respuesta automatica, chupala gil', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'HAB', '2021-11-17 11:06:06', '2021-11-17 11:06:06', NULL),
(27, 3, 'P', '¿Cual es la cotización del día? ', 'NO', NULL, '{\"nombre_usuario\": \"Admin Comercial\"}', 'HAB', '2021-11-17 11:12:53', '2021-11-17 11:12:53', NULL),
(28, 3, 'A', 'La cotización de hoy es de $ 2000 por 1 USD', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'HAB', '2021-11-17 11:12:53', '2021-11-17 11:12:53', NULL),
(29, 3, 'P', '¿Cual es la cotización del día? ', 'NO', NULL, '{\"nombre_usuario\": \"Admin Comercial\"}', 'HAB', '2021-11-17 11:24:53', '2021-11-17 11:24:53', NULL),
(30, 3, 'A', 'La cotización de hoy es de $ 2000 por 1 USD', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'HAB', '2021-11-17 11:24:53', '2021-11-17 11:24:53', NULL),
(31, 3, 'P', 'Callate', 'NO', NULL, '{\"nombre_usuario\": \"Admin Comercial\"}', 'HAB', '2021-11-17 11:48:42', '2021-11-17 11:48:42', NULL),
(32, 3, 'A', 'Yo hago lo que quiero', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'HAB', '2021-11-17 11:48:42', '2021-11-17 11:48:42', NULL),
(33, 4, 'P', 'Hola , estan disponibles', 'NO', NULL, '{\"nombre_usuario\": null}', 'HAB', '2021-11-17 11:54:06', '2021-11-17 11:54:06', NULL),
(34, 4, 'A', 'Hola, esto es una respuesta automatica, chupala gil', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'HAB', '2021-11-17 11:54:06', '2021-11-17 11:54:06', NULL),
(35, 4, 'P', '¿Cual es la cotización del día? ', 'NO', NULL, '{\"nombre_usuario\": \"Admin Comercial\"}', 'HAB', '2021-11-17 11:55:26', '2021-11-17 11:55:26', NULL),
(36, 4, 'A', 'La cotización de hoy es de $ 2000 por 1 USD', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'HAB', '2021-11-17 11:55:26', '2021-11-17 11:55:26', NULL),
(37, 4, 'P', 'cotización', 'NO', NULL, '{\"nombre_usuario\": \"Admin Comercial\"}', 'HAB', '2021-11-17 11:57:14', '2021-11-17 11:57:14', NULL),
(38, 4, 'A', 'La cotización de hoy es de $ 2000 por 1 USD', 'NO', NULL, '{\"nombre_usuario\": \"Respuesta Automatica\"}', 'ELI', '2021-11-17 11:57:14', '2021-11-17 11:57:41', NULL),
(39, 4, 'P', 'adfasfopkñaslfaslfkjas k cotizacion quasjdklsadas', 'NO', NULL, '{\"nombre_usuario\": \"Admin Comercial\"}', 'HAB', '2021-11-17 11:59:06', '2021-11-17 11:59:06', NULL),
(40, 1, 'U', 'Anashe', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-18 10:57:18', '2021-11-18 10:57:18', NULL),
(41, 1, 'U', '321321', 'NO', NULL, '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-18 11:00:23', '2021-11-18 11:00:23', NULL),
(42, 1, 'U', 're', 'NO', '[\"2021-11-18_11-00-39derrcasi.jpg\"]', '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-18 11:00:39', '2021-11-18 11:00:39', NULL),
(43, 1, 'U', 'Anashe', 'NO', '[\"2021-11-18_11-24-082021-11-16_16-11-15Propuesta de proyecto HC (1).pdf\"]', '{\"nombre_usuario\": \"Diego Francisco Romero Maglione\"}', 'HAB', '2021-11-18 11:24:08', '2021-11-18 11:24:08', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
