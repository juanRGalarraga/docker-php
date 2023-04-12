<?php
/*
	Establece los directorios de trabajo del sitio.
	DIR_BASE tiene el path a la raíz del sitio en el sistema de archivos local.
	BASE_URL tiene la URL a la raíz del sitio web.
*/
/*
	Rutas a partir del sistema de archivos.
*/
	/********************************************/
	/*			Directorios particulares 	 	*/
	/********************************************/
	
	define("SYSTEM_DIR","system".DS);
	define("MODEL_DIR","model".DS);
	define("CONTROLLER_DIR","controllers".DS);
	define("CONFIG_DIR","config".DS);
	define("INCLUDES_DIR","includes".DS);

	define("VIEWS_DIR","views".DS);
	define("COMMON_DIR","common".DS);
	define("SITE_DIR","site".DS);
	define("AJAX_DIR","ajax".DS);
	define("CSS_DIR","css".DS);
	define("JS_DIR","js".DS);
	define("IMG_DIR","imgs".DS);
	define("BIBLIOTECA_DIR","library".DS);
	define("LANGS_DIR","langs".DS);
	define("LOGGING_DIR","logs".DS);
	define("BACKEND_DIR","backend".DS);
	define("ERROR_DOCS","errordocs".DS);
	define("TEMP_DIR","temp".DS);
	define("VENDOR_DIR","vendor".DS);
	define("SQL_DIR","base".DS);
	
	/************************************/
	/*			Rutas completas 	 	*/
	/************************************/

	define("DIR_system",DIR_BASE.SYSTEM_DIR);
	define("DIR_model",DIR_system.MODEL_DIR);
	define("DIR_controller",DIR_system.CONTROLLER_DIR);
	define("DIR_config",DIR_system.CONFIG_DIR);
	define("DIR_includes",DIR_system.INCLUDES_DIR);
	define("DIR_sql",DIR_BASE.SQL_DIR);

	define("DIR_views",DIR_BASE.VIEWS_DIR);
	define("DIR_common",DIR_views.COMMON_DIR);
	define("DIR_site",DIR_views.SITE_DIR);
	define("DIR_ajax",DIR_BASE.AJAX_DIR);

	define("DIR_js", DIR_BASE.JS_DIR);
	define("DIR_css", DIR_BASE.CSS_DIR);
	define("DIR_img", DIR_BASE.IMG_DIR);
	define("DIR_imgs", DIR_BASE.IMG_DIR);
	define("DIR_biblioteca", DIR_BASE.BIBLIOTECA_DIR);
	define("DIR_logging",DIR_BASE.LOGGING_DIR);
	define("DIR_backend",DIR_BASE.BACKEND_DIR);
	define("DIR_errordocs",DIR_views.ERROR_DOCS);
	define("DIR_plantillas",DIR_views."plantillas".DS);
	define("DIR_downloader_files",DIR_BASE."downloaded".DS);
	define("DIR_temp",DIR_BASE.TEMP_DIR);
	define("DIR_lang",DIR_includes.LANGS_DIR);
	define("DIR_vendor",DIR_BASE.VENDOR_DIR);	
	
		/************************************/
		/*			Rutas custom			*/
		/************************************/
	// Solamente los directorios 'customizables' están listados aquí.
	if (!empty(DEVELOPE_NAME)) {
		if (FILE_DISP_MODE) {
			// Ej: c:/www/ombucredit/views/site/
			define("DIR_BASE_CUSTOM"      ,DIR_BASE.DEVELOPE_NAME.DS);
			define("DIR_custom_system"    ,DIR_BASE_CUSTOM.SYSTEM_DIR);
			define("DIR_custom_model"     ,DIR_custom_system.MODEL_DIR);
			define("DIR_custom_controller",DIR_custom_system.CONTROLLER_DIR);
			define("DIR_custom_config"    ,DIR_custom_system.CONFIG_DIR);
			define("DIR_custom_includes"  ,DIR_custom_system.INCLUDES_DIR);
			
			define("DIR_custom_views"     ,DIR_BASE_CUSTOM.VIEWS_DIR);
			define("DIR_custom_common"    ,DIR_custom_views.COMMON_DIR);
			define("DIR_custom_site"      ,DIR_custom_views.SITE_DIR);
			
			define("DIR_custom_ajax"      ,DIR_BASE_CUSTOM.AJAX_DIR);
			define("DIR_custom_js"        ,DIR_BASE_CUSTOM.JS_DIR);
			define("DIR_custom_css"       ,DIR_BASE_CUSTOM.CSS_DIR);
			define("DIR_custom_img"       ,DIR_BASE_CUSTOM.IMG_DIR);
			define("DIR_custom_imgs"      ,DIR_BASE_CUSTOM.IMG_DIR);
		} else {
			// Ej: c:/www/views/site/ombucredit/
			define("DIR_custom_system"    ,DIR_system.DEVELOPE_NAME.DS);
			define("DIR_custom_model"     ,DIR_model.DEVELOPE_NAME.DS);
			define("DIR_custom_controller",DIR_controller.DEVELOPE_NAME.DS);
			define("DIR_custom_config"    ,DIR_config.DEVELOPE_NAME.DS);
			define("DIR_custom_includes"  ,DIR_includes.DEVELOPE_NAME.DS);
			
			define("DIR_custom_views"     ,DIR_views.DEVELOPE_NAME.DS);
			define("DIR_custom_common"    ,DIR_common.DEVELOPE_NAME.DS);
			define("DIR_custom_site"      ,DIR_site.DEVELOPE_NAME.DS);
			
			define("DIR_custom_ajax"      ,DIR_ajax.DEVELOPE_NAME.DS);
			define("DIR_custom_js"        ,DIR_js.DEVELOPE_NAME.DS);
			define("DIR_custom_css"       ,DIR_css.DEVELOPE_NAME.DS);
			define("DIR_custom_img"       ,DIR_img.DEVELOPE_NAME.DS);
			define("DIR_custom_imgs"      ,DIR_imgs.DEVELOPE_NAME.DS);
		}
	} else {
		// Y esto es solo para que no queden indefinidas.
		define("DIR_custom_system"    ,DIR_system);
		define("DIR_custom_model"     ,DIR_model);
		define("DIR_custom_controller",DIR_controller);
		define("DIR_custom_config"    ,DIR_config);
		define("DIR_custom_includes"  ,DIR_includes);
		
		define("DIR_custom_views"     ,DIR_views);
		define("DIR_custom_common"    ,DIR_common);
		define("DIR_custom_site"      ,DIR_site);
		
		define("DIR_custom_ajax"      ,DIR_ajax);
		define("DIR_custom_js"        ,DIR_js);
		define("DIR_custom_css"       ,DIR_css);
		define("DIR_custom_img"       ,DIR_img);
		define("DIR_custom_imgs"      ,DIR_imgs);
	}
/*
	Directorio de configuración externo.
	Path al directorio donde se pueden encontrar ciertos archivos de configuración, como access.database.inc.php o wsconfig.inc.php.
	Si no se define, se buscan en DIR_config.
	define("DIR_external_config",DIR_BASE."..".DS.DEVELOPE_NAME.DS);
	*/
/************************************/
/*	URIs a los recursos del sitio.   */
/************************************/


define("BASE_IMG","imgs/");
define("URL_fonts", BASE_URL.'fonts/');
define("URL_img", BASE_URL.BASE_IMG);
define("URL_imgs", BASE_URL.BASE_IMG); // yep, it's the same
define("URL_ajax", BASE_URL.'ajx/');
define("URL_biblioteca", BASE_URL."library/");
define("URL_js", BASE_URL."js/");

		/************************/
		/*		URL Custom		*/
		/************************/
	if (FILE_DISP_MODE) {
		// Solamente las URLs 'customizables' pueden ir aquí.
		define("URL_custom_fonts", BASE_URL.DEVELOPE_NAME.'/fonts/');
		define("URL_custom_img", BASE_URL.DEVELOPE_NAME.'/'.BASE_IMG);
		define("URL_custom_imgs", BASE_URL.DEVELOPE_NAME.'/'.BASE_IMG); // yep, it's the same
	}

if (file_exists("custom_directories.php") and is_file("custom_directories.php")) {
	include("custom_directories.php");
}
?>