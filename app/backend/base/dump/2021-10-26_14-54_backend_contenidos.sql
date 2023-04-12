-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-10-2021 a las 14:53:51
-- Versión del servidor: 8.0.21
-- Versión de PHP: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mecha_backend`
--

--
-- Truncar tablas antes de insertar `backend_contenidos`
--

TRUNCATE TABLE `backend_contenidos`;
--
-- Volcado de datos para la tabla `backend_contenidos`
--

INSERT INTO `backend_contenidos` (`id`, `alias`, `nombre`, `controlador`, `metadata`, `parent_id`, `parametros`, `en_menu`, `orden`, `es_default`, `esta_protegido`, `permit`, `perfiles`, `estado`, `last_modif`, `description`, `sys_fecha_alta`, `sys_fecha_modif`, `sys_usuario_id`) VALUES
(1, '404', 'Error HTTP 404', '404', '{\"js\": \"404\", \"css\": \"404\", \"vista\": \"error404\"}', 0, 0, 0, 999, 0, 0, 0, NULL, 'HAB', '2021-05-04 18:39:07', 'Página de error 404 genérica.', '2021-05-04 18:39:07', '2021-05-04 18:39:07', 1),
(2, 'inicio', 'Dashboard', 'pagina', '{\"js\": \"inicio\", \"css\": \"inicio\", \"vista\": \"inicio\", \"menutag\": \"Dashboard\"}', 0, 0, 1, 1, 0, 1, 0, NULL, 'HAB', NULL, 'Página de inicio.', '2021-05-04 18:40:23', '2021-05-04 18:40:23', 1),
(3, 'login', 'Iniciar sesión', 'login', '{\"js\": \"login\", \"css\": \"login\", \"vista\": \"login\", \"template\": \"login\"}', 0, 0, 0, 2, 0, 0, 0, '', 'HAB', NULL, 'Pantalla de logueo o ingreso al sistema.', NULL, NULL, NULL),
(4, 'logout', 'Cerrar sesión', 'logout', '{}', 0, 0, 0, 3, 0, 0, 0, '', 'HAB', NULL, 'Cierra sesión del usuario.', NULL, NULL, NULL),
(20, 'solicitudes', 'Solicitudes', '', '{\"menutag\": \"Solicitudes\", \"icon_class\": \"fab fa-slideshare\"}', 0, 0, 1, 20, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(21, 'listado', 'Listado de solicitudes', 'pagina', '{\"js\": \"objListados.2.0,solicitudes/listado\", \"css\": \"login\", \"vista\": \"solicitudes/listado\", \"menutag\": \"Listado\", \"icon_class\": \"fad fa-list-alt\"}', 20, 0, 1, 21, 1, 1, 1, NULL, 'HAB', NULL, 'Página de Solicitudes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(25, 'planes', 'Planes', '', '{\"menutag\": \"Planes\", \"icon_class\": \"fas fa-file-powerpoint\"}', 0, 0, 1, 25, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(26, 'listado', 'Lista Planes', 'pagina', '{\"js\": \"planes\", \"css\": \"planes\", \"vista\": \"planes/main\"}', 25, 0, 1, 26, 1, 1, 0, NULL, 'HAB', NULL, 'Página de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(27, 'formulario-plan', 'Formulario plan', 'pagina', '{\"js\": \"planes_form\", \"css\": \"planes_form\", \"vista\": \"planes/newPlanes\", \"menutag\": \"Formulario Planes\"}', 25, 1, 1, 27, 0, 1, 0, NULL, 'HAB', NULL, 'Formulario de Planes.', '2021-09-14 18:40:23', '2021-09-14 18:40:23', 1),
(30, 'usuarios', 'Usuarios', '', '{\"menutag\": \"Usuarios\", \"icon_class\": \"fas fa-users\"}', 0, 0, 1, 10, 0, 1, 0, NULL, 'HAB', NULL, 'Página de Usuarios.', '2021-10-25 18:40:23', '2021-10-25 18:40:23', 1),
(90, 'tests', 'Test varios', '', '{\"menutag\": \"Tests\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 0, 999, 0, 0, 1, NULL, 'HAB', '2021-09-23 07:53:00', 'Raiz para agrupar las diferentes pruebas.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', 1),
(91, 'testmodalbs5', 'Modal para Bootstrap 5', 'pagina', '{\"js\": \"modalBs5,tests/modalbs5\", \"vista\": \"tests/modalbs5\"}', 90, 0, 0, 999, 0, 1, 0, NULL, 'HAB', '2021-09-23 07:53:00', 'Prueba de desarrolo del modal para Bootstrap 5.', '2021-09-23 07:53:00', '2021-09-23 07:53:00', NULL),
(100, 'configuracion', 'Configuración', '', '{\"menutag\": \"Configuración\", \"icon_class\": \"fas fa-cogs\"}', 0, 0, 1, 100, 0, 1, 0, NULL, 'HAB', NULL, 'Configuraciones del sistema', '2021-10-25 08:30:30', '2021-10-25 08:30:30', 1),
(102, 'parametros', 'Parámetros', 'pagina', '{\"js\": \"objListados.2.0,configuraciones/parametros\", \"css\": \"configuraciones/parametros\", \"vista\": \"configuraciones/parametros\", \"menutag\": \"Parámetros generales\", \"icon_class\": \"fas fa-tools\"}', 100, 0, 1, 1, 1, 1, 1, NULL, 'HAB', NULL, 'Ajustar los parámetros generales del sistema.', '2021-10-25 08:30:30', '2021-10-25 08:30:30', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
