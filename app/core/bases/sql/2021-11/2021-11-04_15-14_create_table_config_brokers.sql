DROP TABLE IF EXISTS `config_brokers`;
CREATE TABLE IF NOT EXISTS `config_brokers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `nombre` varchar(64) DEFAULT NULL COMMENT 'Nombre interno del broker',
  `tipo` enum('BRK','BAN','AGN') DEFAULT 'BRK' COMMENT 'Tipo de broker. BRoKer, BANco, AGeNcia',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `data` json DEFAULT NULL COMMENT 'Cualquier otro dato anexo',
  `negocios` json DEFAULT NULL COMMENT 'Contiene un array con los ID de negocios donde este broker está habilitado',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de los brokers de cobro';

--
-- Volcado de datos para la tabla `config_brokers`
--

INSERT INTO `config_brokers` (`id`, `nombre`, `tipo`, `estado`, `data`, `negocios`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'Cobro manual', 'BRK', 'HAB', NULL, '[]', '2021-11-03 10:49:23', '2021-11-03 10:49:23', NULL);
