CREATE TABLE IF NOT EXISTS `ban_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(10) DEFAULT NULL,
  `valor` varchar(255) DEFAULT NULL,
  `expira` datetime DEFAULT NULL COMMENT 'Momento a partir del cual la exclusión deja de tener efecto',
  `estado` enum('HAB','DES','ELI') DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`),
  KEY `expira` (`expira`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Datos que determinan la exclusión del sistema';
