DELETE FROM `backend_contenidos` WHERE `id` = 31;
INSERT INTO `backend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `description`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(31, 'listado', 'Lista usuarios', 'pagina', '{\"js\": \"sweetalert2.all.min,usuarios/usuarios\", \"css\": \"usuarios/usuarios\", \"vista\": \"usuarios/main\"}', 30, 0, 0, 0, 1, 1, 0, NULL, 'HAB', NULL, 'Listado de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1);