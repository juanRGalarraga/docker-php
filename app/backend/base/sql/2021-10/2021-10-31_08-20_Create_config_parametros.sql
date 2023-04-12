-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 31-10-2021 a las 08:21:10
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
-- Base de datos: `mecha_backend`
--

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
  `tipo` enum('INT','FLOAT','STRING','BOOL','MONEDA') DEFAULT 'STRING' COMMENT 'Tipo de dato permitido para la variable',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `permiso` set('ADMIN','OPER') DEFAULT '' COMMENT 'Indica qué rol debe tener el usuario que puede editar este valor',
  `descripcion` text COMMENT 'Descripción del uso de la variable',
  `exponer` tinyint DEFAULT '1' COMMENT '	Indica si este parámetro está expuesto al exterior a través del WS	',
  `ofuscado` tinyint DEFAULT '0' COMMENT 'Mostrar este valor ofuscado en los listados',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de la última modificación',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de alta.',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'ID del usuario que dio el alta o modificó el registro.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `grupo_id` (`grupo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Variables de configuración del sistema';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
