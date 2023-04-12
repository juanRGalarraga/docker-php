<?php
// Nombres de las tablas.
define("TBL_backend_usuarios", "backend_usuarios");
define("TBL_backend_sesiones", "backend_sesiones");
define("TBL_backend_recovery", "backend_recovery");
define("TBL_backend_perfiles", "backend_perfiles");
define("TBL_backend_perfiles_contenidos", "backend_perfiles_contenidos");
define("TBL_backend_permisos", "backend_permisosxusuarios");
define("TBL_contenido", "core_contenidos");
define("TBL_sqlupdate", "sqlupdate");
define("TBL_bancos","config_bancos");

// Usuarios del webservice.
define("TBL_ws_usuarios", "ws_usuarios");

// Parámetros generales del sistema
define("TBL_parametros", "config_parametros");
define("TBL_config_parametros_grupos", "config_parametros_grupos");

// Planes de préstamos y tablas asociadas
define("TBL_planes", "planes");
define("TBL_cargos_impuestos","cargos_impuestos");
define("TBL_cargos_planes","cargos_planes");

// Personas
define("TBL_personas","personas");
define("TBL_personas_data","personas_data");

//Biblioteca
define("TBL_biblioteca","biblioteca");
define("TBL_biblioteca_archivos","biblioteca_archivos");


//Solicitudes
define("TBL_solicitudes","solicitudes");
//Log de solicitudes
define("TBL_solicitudes_log","solicitudes_log");

//banList
define("TBL_bans","ban_list");

// Scores
define("TBL_scores","score_list");

// Tablas de integraciones - infobip
define("TBL_notificador_logs","config_notificador_logs");

// BIND
define("TBL_bind_accounts", "bind_accounts");

define("TBL_visitas", "site_visitas");

//Prestamos
define("TBL_prestamos", "prestamos");
define("TBL_prestamos_hist", "prestamos_hist");
define("TBL_filtros_mora", "config_filtros");

//Cuotas
define("TBL_cuotas", "cuotas");
define("TBL_cuotas_hist", "cuotas_hist");


//GEO
define("TBL_ciudades", "geo_ciudades");
define("TBL_regiones", "geo_regiones");
define("TBL_paises", "geo_paises");


//Cobros
define("TBL_cobros", "cobros");

//Brokers
define("TBL_brokers", "config_brokers");

//Marcas-models-productos
define("TBL_marcas","marcas");
define("TBL_modelos","modelos");
define("TBL_productos","productos");

// Mesa de Incidencias
define("TBL_mesa_incidencia", "mesa_incidencias");

// Tipo de indicencias
define("TBL_tipo_incidencias", "tipos_incidencias");

// Mensaje indicencias
define("TBL_mensajes_incidencias", "mensajes_mesa_indicencias");

// RA indicencias
define("TBL_RA_incidencias", "respuestas_automaticas_tipo_incidencia");
// Seguimientos de gestión de cobro.
define("TBL_seguimientos","gestion_seguimiento");
define("TBL_seguimientos_acciones","gestion_acciones");
