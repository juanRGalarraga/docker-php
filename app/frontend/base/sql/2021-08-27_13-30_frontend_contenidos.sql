-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 27-08-2021 a las 13:29:55
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
  `metadata` text NOT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `parametros` int NOT NULL DEFAULT '0',
  `en_menu` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int NOT NULL DEFAULT '999',
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `esta_protegido` tinyint(1) NOT NULL DEFAULT '0',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `last_modif` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `frontend_contenidos`
--

INSERT INTO `frontend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `estado`, `last_modif`) VALUES
(1, '404', 'Error HTTP 404', '404', '', 0, 0, 0, 1, 0, 0, 'HAB', NULL),
(2, 'inicio', 'Préstamos', 'pagina', '{\r\n\"vista\":\"inicio\",\r\n\"css\":\"inicio\",\r\n\"js\":\"Flow\",\r\n\"menutag\":\"Inicio\",\r\n\"description\":\"Something cool is coming soon\",\r\n\"keywords\":\"Ombú, Vivus, préstamos, online, tarjeta de credito, tarjeta credito, Visa, Credial, Mastercard, Diners, Naranja\",\r\n\"tooltip\":\"Volver al inicio del sistema\",\r\n\"hascarrusel\":true,\r\n\"editable\":true,\r\n\"cotizEnabled\":true\r\n}', 0, 0, 1, 1, 0, 0, 'HAB', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
