DROP TABLE IF EXISTS `permisos_plantillas`;
CREATE TABLE IF NOT EXISTS `permisos_plantillas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) DEFAULT NULL,
  `plantilla` json DEFAULT NULL,
  `estado` enum('HAB','DES','ELI') NOT NULL DEFAULT 'HAB',
  `sys_fecha_alta` datetime DEFAULT NULL,
  `sys_fecha_modif` datetime DEFAULT NULL,
  `sys_usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `permisos_plantillas`
--

INSERT INTO `permisos_plantillas` (`id`, `nombre`, `plantilla`, `estado`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, 'Plantilla01', '[{\"id\": \"2\", \"alias\": \"inicio\", \"nombre\": \"Dashboard\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}, {\"id\": \"20\", \"alias\": \"solicitudes\", \"childs\": [{\"id\": \"21\", \"alias\": \"listado\", \"nombre\": \"Listado de solicitudes\", \"permisos\": [\"r\", \"c\"]}], \"nombre\": \"Solicitudes\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}, {\"id\": \"25\", \"alias\": \"planes\", \"childs\": [{\"id\": \"26\", \"alias\": \"listado\", \"nombre\": \"Lista Planes\"}, {\"id\": \"27\", \"alias\": \"formulario-plan\", \"nombre\": \"Formulario plan\"}], \"nombre\": \"Planes\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}, {\"id\": \"30\", \"alias\": \"usuarios\", \"childs\": [{\"id\": \"31\", \"alias\": \"listado\", \"nombre\": \"Lista usuarios\"}], \"nombre\": \"Usuarios\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}, {\"id\": \"90\", \"alias\": \"tests\", \"childs\": [{\"id\": \"91\", \"alias\": \"testmodalbs5\", \"nombre\": \"Modal para Bootstrap 5\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}], \"nombre\": \"Test varios\"}, {\"id\": \"100\", \"alias\": \"configuracion\", \"childs\": [{\"id\": \"102\", \"alias\": \"parametros\", \"nombre\": \"Parámetros\"}], \"nombre\": \"Configuración\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}, {\"id\": \"103\", \"alias\": \"permisos\", \"childs\": [{\"id\": \"104\", \"alias\": \"editar\", \"nombre\": \"Editar permisos\", \"permisos\": [\"r\", \"c\", \"u\", \"d\"]}], \"nombre\": \"Permisos\", \"permisos\": [\"r\"]}]', 'HAB', '2021-11-02 10:56:46', '2021-11-02 10:56:46', 1);