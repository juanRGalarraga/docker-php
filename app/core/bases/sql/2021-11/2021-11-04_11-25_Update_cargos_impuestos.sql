ALTER TABLE `cargos_impuestos` CHANGE `fechahora` `sys_fecha_alta` DATETIME NULL DEFAULT NULL COMMENT 'fecha y hora de alta', CHANGE `usuario_id` `sys_usuario_id` INT NULL DEFAULT NULL COMMENT 'Usuario que da de alta';
ALTER TABLE `cargos_impuestos` ADD `sys_fecha_modif` DATETIME NULL AFTER `sys_fecha_alta`;