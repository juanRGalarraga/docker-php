DROP TABLE IF EXISTS `bind_accounts`;
CREATE TABLE IF NOT EXISTS `bind_accounts` (
  `id` int NOT NULL,
  `negocio_id` int DEFAULT NULL,
  `account_id` varchar(15) DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `es_default` smallint DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
