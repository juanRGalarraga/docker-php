-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 18-11-2021 a las 18:37:53
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
-- Estructura de tabla para la tabla `respuestas_automaticas_tipo_incidencia`
--

DROP TABLE IF EXISTS `respuestas_automaticas_tipo_incidencia`;
CREATE TABLE IF NOT EXISTS `respuestas_automaticas_tipo_incidencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_id` int(11) NOT NULL,
  `texto_clave` text,
  `mensaje` text,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT CURRENT_TIMESTAMP,
  `sys_fecha_modif` datetime DEFAULT CURRENT_TIMESTAMP,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `respuestas_automaticas_tipo_incidencia`
--

INSERT INTO `respuestas_automaticas_tipo_incidencia` (`id`, `tipo_id`, `texto_clave`, `mensaje`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, NULL, 'Hola, esto es una respuesta automatica, chupala gil', 'HAB', '2021-11-17 00:00:00', '2021-11-17 00:00:00', 1),
(2, 1, '¿Cual es la cotización del hoy?', 'La cotización de hoy es de $ 2000 por 1 USD', 'HAB', '2021-11-17 00:00:00', '2021-11-17 16:36:54', 1),
(3, 1, 'Callate', 'Yo hago lo que quiero', 'HAB', '2021-11-17 00:00:00', '2021-11-17 00:00:00', 1),
(5, 3, 'Hello', 'Mandarina with queso', 'HAB', '2021-11-17 12:38:00', '2021-11-17 12:38:00', NULL),
(6, 3, 'Azulejo', 'Mandarina with queso', 'HAB', '2021-11-17 12:38:10', '2021-11-17 14:13:37', NULL),
(8, 3, NULL, 'Mandarina with queso', 'HAB', '2021-11-17 12:39:08', '2021-11-17 12:39:08', NULL),
(9, 5, NULL, '4124124', 'HAB', '2021-11-17 16:32:52', '2021-11-17 16:32:52', NULL),
(10, 1, 'Messi', '121234214', 'HAB', '2021-11-17 16:36:40', '2021-11-17 16:36:40', NULL),
(11, 5, 'Messi', 'The Best', 'DES', '2021-11-18 08:30:54', '2021-11-18 08:31:03', NULL),
(12, 7, 'Fruteria 23 hs', 'Si vendemos!', 'HAB', '2021-11-18 15:18:50', '2021-11-18 15:18:59', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
