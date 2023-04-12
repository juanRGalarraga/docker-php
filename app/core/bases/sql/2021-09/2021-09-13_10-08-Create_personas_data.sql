CREATE TABLE IF NOT EXISTS `personas_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `persona_id` int DEFAULT NULL COMMENT 'ID de la persona asignada al contacto',
  `tipo` varchar(12) DEFAULT NULL COMMENT 'Tipo de dato guardado',
  `valor` varchar(256) DEFAULT NULL COMMENT 'El valor guardado',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB' COMMENT 'Estado del registro',
  `validado` tinyint DEFAULT NULL COMMENT 'Indica si el registro esta validado o no',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de creación del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de última modificación del registro',
  `sys_usuario_id` int DEFAULT NULL COMMENT 'Usuario que modifico por última ves el registro',
  PRIMARY KEY (`id`),
  KEY `persona_id` (`persona_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
