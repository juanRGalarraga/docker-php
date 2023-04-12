DROP TABLE `solicitudes_log`;
CREATE TABLE `solicitudes_log` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`solicitud_id` INT NULL COMMENT 'Id de la solicitud' ,
	`fechahora` DATETIME(6) NULL COMMENT 'Fecha, hora y milisegundos del evento.' ,
	`tipo_evento` ENUM('ALL','DEBUG','INFO','WARN','ERROR','FATAL','OFF','TRACE') NULL DEFAULT 'INFO' COMMENT 'Calificación del evento' ,
	`paso` VARCHAR(64) NULL COMMENT 'Paso en el onboarding donde ocurrió el evento' ,  
	`data` TEXT NULL COMMENT 'JSON de los datos que participan del evento' ,  
	`descripcion` VARCHAR(512) NULL COMMENT 'Descripción textual del evento' ,  
	`tag` VARCHAR(16) NULL COMMENT 'Marca especial del evento.' ,    
	PRIMARY KEY  (`id`)
) ENGINE = InnoDB COMMENT = 'Log de actividad de las solicitudes.';
ALTER TABLE `solicitudes_log` ADD INDEX (`solicitud_id`, `fechahora`);