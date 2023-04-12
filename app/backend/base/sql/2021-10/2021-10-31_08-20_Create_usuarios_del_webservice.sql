-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 31-10-2021 a las 08:20:02
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
-- Estructura de tabla para la tabla `usuarios_del_webservice`
--

DROP TABLE IF EXISTS `usuarios_del_webservice`;
CREATE TABLE IF NOT EXISTS `usuarios_del_webservice` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID del usuario. Uso interno',
  `negocio_id` int DEFAULT NULL COMMENT 'Negocio al cual pertenece este usuario del webservice',
  `username` varchar(128) DEFAULT NULL COMMENT 'Nombre de usuario de la API',
  `password` varchar(128) DEFAULT NULL COMMENT 'Contraseña asociada',
  `config` json DEFAULT NULL COMMENT 'Cualquier dato de configuración necesario',
  `modo` enum('TEST','PROD') DEFAULT NULL COMMENT 'Modo en que actúa el usuario en el sistema.',
  `bodyencodingkey` varchar(256) DEFAULT NULL COMMENT 'Esta es la clave de cifrado del cuerpo de las peticiones que este usuario usa para cifrar los mensajes que envía al servidor.',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de usuarios de la API';

--
-- Volcado de datos para la tabla `usuarios_del_webservice`
--

INSERT INTO `usuarios_del_webservice` (`id`, `negocio_id`, `username`, `password`, `config`, `modo`, `bodyencodingkey`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 1, 'mechabackenduser', 'j9GketlvS', '{\"tsession\": \"3600\"}', 'PROD', 'd2Iclk357o6EzAbtSr4nXf19jgvy8', '2021-10-30 17:12:56', '2021-10-30 17:12:56', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
