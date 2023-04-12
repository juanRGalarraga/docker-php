
DROP TABLE IF EXISTS `frontend_solicitudes`;
CREATE TABLE IF NOT EXISTS `frontend_solicitudes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` json DEFAULT NULL COMMENT 'Los datos proporcionados por el visitante para la solicitud.',
  `estado` enum('INIT','ENDOK','ENDFAIL','HOLD') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'INIT' COMMENT 'El estado actual de la solicitud.',
  `alias_paso` varchar(64) DEFAULT NULL COMMENT 'El alias del último paso visitado (NO ES el control de pasos del onboarding!)',
  `ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'IP del visitante',
  `user_agent` VARCHAR(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'El User-Agent del navegador del visitante.',
  `core_last_command` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Último comando enviado al core (truncada)',
  `core_last_response` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Última respuesta del core (truncada)',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha y hora de creación',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación al registro',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Solicitudes del visitante.';