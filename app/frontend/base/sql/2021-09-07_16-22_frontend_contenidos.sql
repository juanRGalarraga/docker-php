-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 07-09-2021 a las 16:22:14
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
-- Base de datos: `tenela_2021_front`
--

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
-- Volcado de datos para la tabla `frontend_contenidos`
--

INSERT INTO `frontend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `estado`, `sys_fecha_modif`, `sys_fecha_alta`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', NULL, 0, 0, 0, 1, 0, 0, 'HAB', '2021-08-29 17:39:33', '2021-08-29 17:39:33', NULL),
(2, 'inicio', 'Préstamos', 'pagina', '{\"js\": \"calculadora,onboarding\", \"css\": \"inicio\", \"vista\": \"inicio\", \"menutag\": \"Inicio\", \"tooltip\": \"Volver al inicio del sistema\", \"keywords\": \"Ombú, Vivus, préstamos, online, tarjeta de credito, tarjeta credito, Visa, Credial, Mastercard, Diners, Naranja, Afluenta\", \"onboarding\": true, \"calculadora\": \"visible\", \"description\": \"Something cool is coming soon\"}', 0, 0, 1, 1, 0, 0, 'HAB', '2021-08-29 17:39:33', '2021-08-29 17:39:33', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
