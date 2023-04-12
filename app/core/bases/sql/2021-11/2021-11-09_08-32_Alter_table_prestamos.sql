ALTER TABLE `prestamos` ADD `tipo_prestamo` ENUM('UNICO','CUOTAS') NOT NULL DEFAULT 'UNICO' COMMENT 'Indica si el préstamo tiene cuotas o es de pago único' AFTER `plan_id`;
