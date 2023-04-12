<?php
/*
	Archivo de inicialización.
*/
if (version_compare(phpversion(),'7.2.0','<')) {
	die("Can't run on PHP older than 7.2.0, sorry...");
}

/*
	Estando en Apache, éste puede ser configurado con variables de entorno:
		SetEnv NOMBRE_VARIABLE valor
	El cual será tomando en cuenta por este script para determinar el modo, cliente y ambiente en el cual se ejecuta.
*/

// Si no está establecida la variable $_SERVER['DEVELOPE_NAME'] en el Virtual Host, por omisión es Metropol, que es la base de todo el proyecto.
	defined('DEVELOPE_NAME') || define('DEVELOPE_NAME',(isset($_SERVER['DEVELOPE_NAME'])) ? $_SERVER['DEVELOPE_NAME']: '');

// Si no está establecida la variable $_SERVER['DEPLOY'] en el Virtual Host, por omisión se establece en local.
	defined('DEPLOY') || define('DEPLOY',(isset($_SERVER['DEPLOY'])) ? strtolower($_SERVER['DEPLOY']): 'local');

// Si no se estableció el modo de interfaz, por omisión será 'core'.
	defined('INTERFACE_MODE') || defined('INTERFACE_MODE') || define('INTERFACE_MODE',(isset($_SERVER['INTERFACE_MODE'])) ? strtolower($_SERVER['INTERFACE_MODE']): 'core');

// Qué modo de disposición de archivos dentro del sistema se va a usar. Indica si los archivos custom se pondrán todos en un mismo directorio (true) o bién cada parte del sistema tendrá un directorio propio para cada cliente (false).
	define("FILE_DISP_MODE", false);

/* Si se está en local o ambiente test/dev, mostrar mensajes de error de PHP explícitos */
	if (in_array(strtolower(DEPLOY), ['local','test','dev'])) {
		define("DEVELOPE",true);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	} else { // caso contrario, nones.
		define("DEVELOPE",false);
		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);
	}

/* El nombre del archivo de configuración para la actualización de la base de datos */
	define("BDDACTUALIZAR_FILE","BddActualizar.json");

/* El nombre del archivo que contiene la pimienta para el cifrado de las contraseñas */
	define("PEPPER_FILE","pepper.txt");

/* La hora y fecha es la de Argentina. */
	date_default_timezone_set('America/Argentina/Buenos_Aires');
	// México: date_default_timezone_set('America/Mexico_City');
	// Colombia: date_default_timezone_set('America/Bogota');
	// España: date_default_timezone_set('Europe/Madrid');

/* Acortar el nombre de la constante separador de directorio del sistema operativo. */
	define("DS",DIRECTORY_SEPARATOR);

/* Esto establece la raíz donde está el desarrollo actual respecto del sistema de archivo local. */
	define("DIR_BASE",__DIR__.DS);

/* Asegurar que PHP trabaje en UTF-8. */
	mb_internal_encoding('UTF-8');
	mb_regex_encoding('UTF-8');

/* Estirar el tiempo de vida de las cookies y de la sesión PHP a una hora */
	defined("COOKIES_MAXLIFETIME") || define("COOKIES_MAXLIFETIME",3600);
	$maxlifetime = (int)ini_get('session.gc_maxlifetime');
	if ($maxlifetime != COOKIES_MAXLIFETIME) {
		ini_set('session.gc_maxlifetime',COOKIES_MAXLIFETIME);
	}
	$maxlifetime = (int)ini_get('session.cookie_lifetime');
	if ($maxlifetime != COOKIES_MAXLIFETIME) {
		ini_set("session.cookie_lifetime",COOKIES_MAXLIFETIME);	
	}
	ini_set('session.gc_maxlifetime',COOKIES_MAXLIFETIME);
	ini_set("session.cookie_lifetime",COOKIES_MAXLIFETIME);	

/* Normalizar el Fin De Línea 'FDL' */
	$FDL = (isset($_SERVER['SERVER_NAME']))?'<br />'.PHP_EOL:PHP_EOL;
	define("FDL",$FDL);

/*
				 Ahora vamos con el tratamiento de las URLs.
*/
// Directorio virtual al que fue llamado el sitio.
$base = dirname($_SERVER["SCRIPT_NAME"]);
$base = str_replace('\\','/',trim($base));
/*
 Determina si se está en la raiz virtual del servidor web o en uno de sus directorios.
 En cualquier caso, se usa esa información para establecer la raiz desde la cual llamar al resto de los contenidos.
*/
$intercec = array_intersect_ex( explode("/",str_replace('\\','/',trim(DIR_BASE))), explode("/",$base) ); // ¿Qué tienen en común DIR_BASE y $base?
$base = '/';
foreach ($intercec as $value) { // Volver a intercalar la / en cada uno de los directorios para formar así la URL.
	if (!empty($value)) {
		$base .= $value.'/';
	}
}

if (isset($base[0]) and ($base[0] != "/"))  { $base = "/".$base; } // Asegurarse que $base tenga la primera / para no tener problemas después.

$scheme = (isHttps())?"https":"http"; // ¿El servidor puede gestionar una conexión segura?, ¿el contenido se pidió de tal forma?.

/* 		La URL base del sitio	 */
$dominio = (!empty($_SERVER['HTTP_HOST']))?$_SERVER['HTTP_HOST']:'localhost';
define('BASE_URL', $scheme.'://'.$dominio.$base);  // Establecer cuál es la URL base del sitio.


/* Establecer los directorios de trabajo del sitio */
if (!is_file(DIR_BASE."directories.php") or !is_readable(DIR_BASE."directories.php")) { die("No puedo acceder al archivo de constantes de directorio directories.php"); }
require_once(DIR_BASE."directories.php");

// Configuración de la base de datos
$database_config = LoadConfig('database', DIR_custom_config);
if (!is_file($database_config) or !is_readable($database_config)) {
	$database_config = LoadConfig('access.database', DIR_custom_config);
	if (!is_file($database_config) or !is_readable($database_config)) {
		die("No puedo acceder al archivo de credenciales del DBM ".((DEVELOPE)?$database_config:"database.cfg.php"));
	}
}
require_once($database_config);
unset($database_config);

$devmyfile = "..".DS."metropol.php";
if (!empty(DEVELOPE_NAME)) {
	$devmyfile = "..".DS.DEVELOPE_NAME.".php";
}
if (file_exists($devmyfile) and is_file($devmyfile) and is_readable($devmyfile)) {
	include($devmyfile);
}
/* Establecer los parámetros para la cookie de sesión */
if (version_compare(PHP_VERSION, '7.13.0') >= 0) { // A partir de PHP 7.13...
	session_set_cookie_params(array('samesite'=>'lax')); 
} else {
	session_set_cookie_params(time()+$maxlifetime, '/;samesite=lax', @$_SERVER['HTTP_HOST'], false, true);
}

/*
	Determina si la petición HTTP se hizo en una conexión segura. Incluso si fue a través de un PROXY.
*/
function isHttps() {
	$result = false;
    if (array_key_exists("HTTPS", $_SERVER) and (strtolower($_SERVER["HTTPS"]) === 'on')) {
		$result = true;
    }
    if (array_key_exists("SERVER_PORT", $_SERVER) and ((int)$_SERVER["SERVER_PORT"] === 443)) {
		$result = true;
    }
    if (array_key_exists("HTTP_X_FORWARDED_SSL", $_SERVER) and (strtolower($_SERVER["HTTP_X_FORWARDED_SSL"]) === 'on')) {
		$result = true;
    }
    if (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) and (strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) === 'https')) {
		$result = true;
    }
    return $result;
}
/*
	Devuelve un array con los elementos en común de los dos arrays que se pasan como parámetro... pero insensible a mayúsculas.
*/
function array_intersect_ex($a, $b) {
	$return = array();
	foreach ($a as $needle) {
		if (array_search(strtolower($needle),array_map('strtolower',$b)) !== FALSE) {
			$return[] = $needle;
		}
	}
	return $return;
}
/*
	Si el nombre que se pasa en $archivo está vacío, se le agrega .cfg.php por omisión.
	Suponiendo que DIR_BASE vale 'c:/htdocs/www/metropol/'
	DEVELOPE_NAME vale 'mecha'
	INTERFACE_MODE vale 'frontend'
	Y $archivo 'target.cfg'
*/
function LoadConfig($archivo, $default = null) {
	$ext = pathinfo($archivo, PATHINFO_EXTENSION);
	if (empty($ext)) { $archivo .= '.cfg.php'; }

	$result = $default.$archivo;
	$OneUp = DIR_BASE.'..'.DS;                                                        // c:/htdocs/www/
	$paths = array();
	$paths[] = DIR_config.$archivo;                                                   // c:/htdocs/www/metropol/system/config/target.cfg
	$paths[] = $OneUp.$archivo;                                                       // c:/htdocs/www/target.cfg
	$paths[] = $OneUp.'config'.DS.$archivo;                                           // c:/htdocs/www/config/target.cfg
	$paths[] = $OneUp.'config'.DS.DEVELOPE_NAME.DS.$archivo;                          // c:/htdocs/www/config/mecha/target.cfg
	if (defined("INTERFACE_MODE") and (INTERFACE_MODE != '')) {
		$paths[] = $OneUp.'config'.DS.INTERFACE_MODE.DS.$archivo;                     // c:/htdocs/www/config/frontend/target.cfg
		$paths[] = $OneUp.'config'.DS.DEVELOPE_NAME.DS.INTERFACE_MODE.DS.$archivo;    // c:/htdocs/www/config/mecha/frontend/target.cfg
	}
	$paths[] = $OneUp.DEVELOPE_NAME.DS.$archivo;                                      // c:/htdocs/www/mecha/target.cfg
	$paths[] = $OneUp.DEVELOPE_NAME.DS.'config'.DS.$archivo;                          // c:/htdocs/www/mecha/config/target.cfg
	if (defined("INTERFACE_MODE") and (INTERFACE_MODE != '')) {
		$paths[] = $OneUp.DEVELOPE_NAME.DS.'config'.DS.INTERFACE_MODE.DS.$archivo;    // c:/htdocs/www/mecha/config/frontend/target.cfg
	}
	// echo '<pre>';print_r($paths);echo '</pre>';
	foreach($paths as $path) {
		if (file_exists($path) and is_file($path) and is_readable($path)) {
			$result = $path;
			//echo $result;
			break;
		}
	}
	return $result;
}
