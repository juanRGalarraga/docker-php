DROP TABLE IF EXISTS `biblioteca_archivos`;
CREATE TABLE IF NOT EXISTS `biblioteca_archivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `biblioteca_id` int(11) DEFAULT NULL COMMENT 'ID de la biblioteca (y por ende de la persona)',
  `nombre` varchar(255) DEFAULT NULL COMMENT 'Nombre físico del archivo',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado de habilitación',
  `procesado` tinyint(1) DEFAULT '0',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persona_id` (`biblioteca_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Lista de archivos.';