CREATE TABLE IF NOT EXISTS `solicitudes_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitud_id` int(11) DEFAULT NULL COMMENT 'ID de la solicitud de la cual es el log',
  `tipo` enum('ALL','DEBUG','INFO','WARN','ERROR','FATAL','OFF','TRACE') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'INFO' COMMENT 'Tipo de log',
  `texto` varchar(255) DEFAULT NULL COMMENT 'Texto descriptivo',
  `data` json DEFAULT NULL COMMENT 'Posibles datos que se insertaron en la solicitud',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de alta del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de la última modificación del registro',
  `sys_usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que realizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `solicitud_id` (`solicitud_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;