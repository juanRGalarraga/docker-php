CREATE TABLE IF NOT EXISTS `core_contenidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alias` varchar(50) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `controlador` varchar(50) NOT NULL,
  `metadata` json NOT NULL,
  `parent_id` int NOT NULL DEFAULT '0',
  `parametros` int NOT NULL DEFAULT '0',
  `en_menu` tinyint(1) NOT NULL DEFAULT '0',
  `orden` int NOT NULL DEFAULT '999',
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `esta_protegido` tinyint(1) NOT NULL DEFAULT '0',
  `permit` tinyint(1) DEFAULT '0' COMMENT 'Indica si el contenido entra en la zona de permisos',
  `perfiles` set('ADMIN','OPER') DEFAULT NULL COMMENT 'En qué perfiles de usuario aparece este contenido',
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `last_modif` datetime DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Describe de qué se trata este contenido',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alias` (`alias`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='Contenidos del core';

--
-- Volcado de datos para la tabla `core_contenidos`
--

INSERT INTO `core_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `descripcion`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(2, 'inicio', 'Página de Inicio', 'pagina', '{\"js\": \"inicio,y,otras,cosas,bonitas\", \"css\": \"inicio\", \"vista\": \"inicio\"}', 0, 0, 1, 999, 0, 0, 0, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1),
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1);
