ALTER TABLE `prestamos` ADD `tipo_prestamo` ENUM('UNICO','CUOTAS') NULL COMMENT 'Indica si el préstamo tiene cuotas o es a pago único.' AFTER `calculo`;