-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelos`
--

DROP TABLE IF EXISTS `modelos`;
CREATE TABLE IF NOT EXISTS `modelos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marca_id` int(11) DEFAULT NULL COMMENT 'ID de la marca a la que pertenece',
  `nombre` varchar(64) DEFAULT NULL COMMENT 'Nombre del modelo',
  `descripcion` varchar(128) DEFAULT NULL COMMENT 'Descripción del modelo',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado',
  `data` json DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marca_id` (`marca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;