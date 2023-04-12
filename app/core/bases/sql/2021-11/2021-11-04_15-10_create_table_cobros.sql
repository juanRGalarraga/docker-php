
DROP TABLE IF EXISTS `cobros`;
CREATE TABLE IF NOT EXISTS `cobros` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `prestamo_id` int(11) DEFAULT NULL COMMENT 'ID del préstamo que se está cobrando',
  `cuota_id` int(11) DEFAULT NULL COMMENT 'ID de la cuota (puede ser null))',
  `persona_id` int(11) DEFAULT NULL COMMENT 'ID de la persona, por conveniencia',
  `broker_id` int(11) DEFAULT NULL,
  `estado` enum('PEND','ACRE','RECH','ANUL') DEFAULT NULL COMMENT 'PENDiente de confirmación, ACREditado, RECHazado, ANULado',
  `fecha_de_cobro` date DEFAULT NULL COMMENT 'La fecha de cobro efectivo',
  `monto` decimal(16,4) DEFAULT NULL COMMENT 'El monto del cobro',
  `nro_comprobante` varchar(25) DEFAULT NULL COMMENT 'El número de comprobanto',
  `archivo_id` int(11) DEFAULT NULL COMMENT 'ID del archivo físico en la bilioteca',
  `tipo_moneda` varchar(3) DEFAULT 'ARS' COMMENT 'En qué moneda se realizó el cobro',
  `data` json DEFAULT NULL,
  `factura_id` varchar(100) DEFAULT NULL,
  `motivo_rechazo` varchar(54) DEFAULT NULL COMMENT 'Motivo por el cual fue rechazado un cobro',
  `fecha_rechazo` date DEFAULT NULL COMMENT 'fecha en que fue rechazado un cobro',
  `sys_fecha_alta` datetime DEFAULT NULL COMMENT 'Fecha de registración del cobro',
  `sys_fecha_modif` datetime DEFAULT NULL COMMENT 'Fecha y hora de la última modificación al registro',
  `sys_usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario del sistema que realizó la última modificación',
  PRIMARY KEY (`id`),
  KEY `prestamo_id` (`prestamo_id`),
  KEY `persona_id` (`persona_id`),
  KEY `broker_id` (`broker_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
