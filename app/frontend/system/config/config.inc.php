<?php
/*
	Configuración general del desarrollo.
*/
// Listado de las tablas de la DB.
include("database.config.inc.php");

// Título por omisión del sitio.
defined("MAINTITLE") || define("MAINTITLE","Rebrit Generator 1.80");
// Nombre de la aplicación (no del cliente).
defined("APP_NAME") || define("APP_NAME","RebritGenerator 1.8");
defined("APP_VERSION_NUMBER") || define("APP_VERSION_NUMBER","v1.8");
defined("APP_DESCRIPTION") || define("APP_DESCRIPTION","Framework for web site generation");

// El tipo de interfaz de la cual estamos hablando
defined("INTERFACE_TYPE") || define("INTERFACE_TYPE","frontend");

// Las cosas lindas del sitio.
defined("SITE_favicon") || define("SITE_favicon",URL_img."favicon.png");
defined("SITE_mainlogo") || define("SITE_mainlogo",URL_img."mainlogo.png");
defined("SITE_menulogo") || define("SITE_menulogo",URL_img."menulogo.png");
defined("SITE_humans") || define("SITE_humans","humans.txt");

// Archivos del tinglado
define("DEFAULT_SITE_TEMPLATE","main");
define("DEFAULT_SITE_HEAD","head");
define("DEFAULT_SITE_HEADER","header");
define("DEFAULT_SITE_FOOTER","footer");
define("DEFAULT_SITE_MENU","mainmenu");
define("DEFAULT_SITE_SUB_MENU","submenu");
defined("DEFAULT_CONTENT") || define("DEFAULT_CONTENT","inicio");

// Nombre del archivo (físico) que tiene la lista de archivos de assets para cargar automáticamente (NO incluir directorio!)
define("JS_LIST_FILES","required_scripts.lst"); // Archivos JavaScript
define("CSS_LIST_FILES","required_styles.lst"); // Archivos CSS


/*
	MUY importante
	Esta es la lista de aliases restringidos. Tienen un significado especial.
	Los aliases listados aquí no se validan contra la base de datos sino que redirigen a directorios donde se tratan por separado.
*/
const ASSETS_CONTENTS = array(
	'css'=>array('dir'=>DIR_css, 'controller'=>'css'),
	'js'=> array('dir'=>DIR_js,  'controller'=>'js'),
	'ajx'=>array('dir'=>DIR_ajax,'controller'=>'ajax')
);

// Alias especial para ignorar la carga de contenido desde la base de datos, es para pedir archivos assets directamente.
defined("DEFAULT_FOR_FILES") || define("DEFAULT_FOR_FILES","f");

// Estos directorios AJAX está desmilitarizados
define('DMZ_CONTENTS',["watch".DS]);
// Estos archivos AJAX están desmilitarizados
define('DMZ_ARCHIVOS',["checkLogin"]);

define("SQLUP", "sql");
?>