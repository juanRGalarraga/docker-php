CREATE TABLE IF NOT EXISTS `solicitudes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `negocio_id` int(11) DEFAULT NULL COMMENT 'ID del negocio al que pertenece la solicitud',
  `estado` enum('HAB','DES','ELI') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'HAB' COMMENT 'Estado de validez de la solicitud',
  `estado_solicitud` enum('PEND','APRO','RECH','FAIL','ANUL') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'PEND' COMMENT 'Estado actual de la solicitud',
  `prestamo_id` int(11) DEFAULT NULL COMMENT 'El ID del préstamo que fue generado por esta solicitud',
  `persona_id` int(11) DEFAULT NULL COMMENT 'El ID de la persona asignada a esta solicitud',
  `data` json DEFAULT NULL,
  `origen` varchar(32) DEFAULT NULL COMMENT 'Desde donde provino la solicitud',
  `producto_id` int(11) DEFAULT NULL COMMENT 'ID del producto si corresponde',
  `ws_usuario_id` int(11) DEFAULT NULL COMMENT 'ID del usuario del webService que genero la solicitud',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de creación del registro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha de última modificación del registro',
  `sys_usuario_id` datetime DEFAULT NULL COMMENT 'ID del usuario que realizo la última modificación',
  PRIMARY KEY (`id`),
  KEY `negocio_id` (`negocio_id`),
  KEY `prestamo_id` (`prestamo_id`),
  KEY `persona_id` (`persona_id`),
  KEY `sys_fecha_alta` (`sys_fecha_alta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;