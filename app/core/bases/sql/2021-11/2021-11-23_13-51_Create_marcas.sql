-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(64) DEFAULT NULL COMMENT 'Nombre de la marca',
  `descripcion` varchar(128) DEFAULT NULL COMMENT 'Descripción de la marca',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado',
  `data` json DEFAULT NULL COMMENT 'Datos extra',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
