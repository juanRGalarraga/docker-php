-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 23-08-2021 a las 07:34:41
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
-- Base de datos: `rbt_200`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tabla_de_pruebas`
--

DROP TABLE IF EXISTS `tabla_de_pruebas`;
CREATE TABLE IF NOT EXISTS `tabla_de_pruebas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) DEFAULT NULL,
  `monto` decimal(13,4) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `data` json DEFAULT NULL,
  `flag1` tinyint(1) DEFAULT '0',
  `relleno` int DEFAULT NULL,
  `tipo_moneda` varchar(3) DEFAULT 'ARS',
  `mas_relleno` text,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tabla_de_pruebas`
--

INSERT INTO `tabla_de_pruebas` (`id`, `nombre`, `monto`, `estado`, `data`, `flag1`, `relleno`, `tipo_moneda`, `mas_relleno`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'Esto es un nombre cualquiera.', '1014.0000', 'HAB', '{\"ab\": \"Veamos cómo sale.\", \"unapropiedad\": \"Otro nuevo valor\", \"otra_propiedad\": \"La nueva propiedad\", \"relleno_anterior\": \"470\"}', 1, 471, 'ARS', 'Esto es simplemente un texto.', '2021-08-21 19:03:53', '2021-08-22 09:55:50', 1),
(2, NULL, '2630.6667', 'HAB', '{\"relleno_anterior\": \"2\"}', 0, 3, 'ARS', NULL, '2021-08-22 10:07:10', '2021-08-23 07:26:47', NULL),
(3, 'Rebrit SRL', '2000.0000', 'HAB', '{\"lapropiedad\": \"Con su valor\"}', 0, NULL, 'ARS', 'Hola a todo el mundo.', '2021-08-23 06:15:07', NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
