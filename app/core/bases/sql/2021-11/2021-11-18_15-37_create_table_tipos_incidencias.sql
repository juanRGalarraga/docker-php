-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 18-11-2021 a las 18:37:19
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
-- Estructura de tabla para la tabla `tipos_incidencias`
--

DROP TABLE IF EXISTS `tipos_incidencias`;
CREATE TABLE IF NOT EXISTS `tipos_incidencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `sitio` enum('ALL','MIC') DEFAULT 'ALL',
  `estado` enum('HAB','DES','ELI') DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT CURRENT_TIMESTAMP,
  `sys_fecha_modif` datetime DEFAULT CURRENT_TIMESTAMP,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tipos_incidencias`
--

INSERT INTO `tipos_incidencias` (`id`, `nombre`, `sitio`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'Incds', 'ALL', 'HAB', '2021-11-15 14:35:15', '2021-11-15 14:42:28', NULL),
(2, 'Manija', 'ALL', 'HAB', '2021-11-15 14:37:33', '2021-11-15 14:37:33', NULL),
(3, 'Vinos', 'ALL', 'HAB', '2021-11-17 10:36:14', '2021-11-17 10:36:14', NULL),
(4, 'Moral SA', 'ALL', 'HAB', '2021-11-17 10:38:37', '2021-11-17 10:38:37', NULL),
(5, 'Tomi', 'MIC', 'HAB', '2021-11-17 10:39:27', '2021-11-17 10:48:53', NULL),
(6, 'Dulces', 'ALL', 'DES', '2021-11-17 10:40:15', '2021-11-17 10:44:03', NULL),
(7, 'La nueva nueva', 'ALL', 'HAB', '2021-11-18 15:18:29', '2021-11-18 15:18:29', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
