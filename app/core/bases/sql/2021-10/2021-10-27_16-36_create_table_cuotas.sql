DROP TABLE IF EXISTS `cuotas`;
CREATE TABLE IF NOT EXISTS `cuotas` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único',
  `ref_cuota` varchar(16) DEFAULT NULL COMMENT 'Un número que sería el alias de la cuota compuesto por el ID de préstamo, cuota número y ID de persona',
  `prestamo_id` int(11) DEFAULT NULL COMMENT 'ID del préstamo al que pertenece la cuota',
  `cuota_nro` int(11) DEFAULT NULL COMMENT 'Número de cuota, también número de ordenación de cuota',
  `persona_id` int(11) DEFAULT NULL COMMENT 'ID de la persona deudora de la cuota, por conveniencia',
  `monto_cuota` decimal(16,4) DEFAULT NULL COMMENT 'Monto calculado a pagar de la cuota',
  `tipo_moneda` varchar(4) DEFAULT 'MXN' COMMENT 'La moneda con la que se debe interpretar los montos',
  `estado` enum('PEND','CANC','MORA','REFIN','HOLD','PAGP','DIFCAP','PROR','PAGCAP','PAGINT') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'PEND' COMMENT 'Estado de habilitación',
  `estado_ant` enum('SOLIC','PEND','CANC','MORA','REFIN','HOLD','PAGP','DIFCAP','PROR','PAGCAP','PAGINT') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Estado anterior del préstamo',
  `fecha_venc` date DEFAULT NULL COMMENT 'Fecha de vencimiento de la cuota',
  `dias` int(11) DEFAULT NULL COMMENT 'Dias transcurridos con la cuota anterior',
  `interes_cuota` decimal(16,4) DEFAULT NULL COMMENT 'Monto interés cobrado en esta cuota',
  `iva_interes_cuota` decimal(16,4) DEFAULT NULL COMMENT 'El IVA sobre el interés cobrado en la cuota',
  `capital` decimal(16,4) DEFAULT NULL COMMENT 'Monto_cuota menos interes_cuota',
  `fecha_cobro` datetime DEFAULT NULL COMMENT 'fecha de cobro de la cuota',
  `cobro_id` int(11) DEFAULT NULL COMMENT 'ID del comprobante de cobro',
  `monto_mora` decimal(16,4) DEFAULT NULL COMMENT 'Monto del sobrecargo por mora ',
  `total_iva_mora` decimal(16,4) DEFAULT NULL COMMENT 'El IVA que se cobra sobre el monto de la mora ',
  `saldo_inicio_periodo` decimal(16,4) DEFAULT NULL COMMENT 'Saldo al inicio de la cuota ',
  `saldo_final_periodo` decimal(16,4) DEFAULT NULL COMMENT 'Saldo al final de la cuota',
  `va_a_mora` enum('SI','NO') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'SI' COMMENT 'Indica si la cuota actual va a mora o se traspasa su fecha de vencimiento',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prestamo_id` (`prestamo_id`,`cuota_nro`),
  KEY `estado` (`estado`,`fecha_venc`),
  KEY `ref_cuota` (`ref_cuota`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cuotas de los préstamos';