-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelo_id` int(11) DEFAULT NULL COMMENT 'ID del modelo del cual es el producto',
  `nombre` varchar(64) DEFAULT NULL COMMENT 'Nombre del producto si es que lo tiene',
  `precio` decimal(13,4) DEFAULT NULL COMMENT 'Precio unitario',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado',
  `data` json DEFAULT NULL COMMENT 'Datos extra',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `modelo_id` (`modelo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;