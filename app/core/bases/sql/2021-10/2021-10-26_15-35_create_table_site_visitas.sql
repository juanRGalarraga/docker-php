DROP TABLE IF EXISTS `site_visitas`;
CREATE TABLE IF NOT EXISTS `site_visitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitas` int(11) DEFAULT NULL,
  `dominio` varchar(255) DEFAULT NULL,
  `pagina` varchar(255) DEFAULT NULL,
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
