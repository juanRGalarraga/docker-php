-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 18-11-2021 a las 18:35:32
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
-- Estructura de tabla para la tabla `mesa_incidencias`
--

DROP TABLE IF EXISTS `mesa_incidencias`;
CREATE TABLE IF NOT EXISTS `mesa_incidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `persona_id` int(11) DEFAULT NULL,
  `usuarios` json DEFAULT NULL,
  `tipo_id` int(11) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `prioridad` enum('NUEVA','BAJA','NORMAL','ALTA','URGENTE','INMEDIATA') NOT NULL,
  `estado` enum('NUEVA','MASINF','ACEPTADA','CONFIRMADA','ASIGNADA','RESUELTA','CERRADA') NOT NULL,
  `sys_fecha_alta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sys_fecha_modif` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `mesa_incidencias`
--

INSERT INTO `mesa_incidencias` (`id`, `persona_id`, `usuarios`, `tipo_id`, `asunto`, `prioridad`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, '[\"1\", \"2\"]', 1, 'Hola perri', 'NORMAL', 'NUEVA', '2021-11-15 15:46:03', '2021-11-18 15:06:27', NULL),
(2, 1, '[\"2\"]', 1, 'Incds', 'NUEVA', 'NUEVA', '2021-11-17 11:05:00', '2021-11-17 11:05:00', NULL),
(3, 1, '[\"1\", \"2\"]', 1, 'Que tema che!', 'NUEVA', 'NUEVA', '2021-11-17 11:06:06', '2021-11-18 14:51:30', NULL),
(4, 2, '[\"1\"]', 1, 'Incds', 'URGENTE', 'ACEPTADA', '2021-11-17 11:54:06', '2021-11-18 15:15:26', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
