<?php
/*
	Inicio del Framework RebritGenerator 2.0
	Author: DriverOp.
	Created: 2021-08-15
*/

define("BASE_VPATH","_virualpath_"); // Es el path virtual establecido en el archivo .htaccess

header("Cache-Control: no-cache, must-revalidate"); // No catchear el contenido
header("X-Frame-Options: SAMEORIGIN"); // Incluir el contenido en iframe solo desde el mismo dominio (evita el clickjacking).
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");
header("Permissions-Policy: geolocation=(self)");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self' data: blob: filesystem: about: ws: wss: 'unsafe-inline' 'unsafe-eval'; script-src * data: blob: 'unsafe-inline' 'unsafe-eval'; connect-src * data: blob: 'unsafe-inline'; img-src * data: blob: 'unsafe-inline'; frame-src * data: blob: ; style-src * data: blob: 'unsafe-inline';font-src * data: blob: 'unsafe-inline'; frame-ancestors * data: blob: 'unsafe-inline';");
// Inicializar todas las constantes para comenzar a laburar...
if (!is_file('initialize.php') or !is_readable('initialize.php')) { die("No puedo acceder al archivo de inicialización general initialize.php"); }
require_once('initialize.php');
session_start();
// Sobreescribir la cookie de sesión PHP con nuestras propias opciones
setcookie(session_name(), session_id(),	array(
		'expires' => time()+COOKIES_MAXLIFETIME,
		'path' => '/',
		'domain' => $_SERVER['HTTP_HOST'],
		'secure' => $scheme == 'https',
		'httponly' => true,
		'samesite' => 'lax'
	)
);
// Establecer las constantes de configuración
// Del cliente primero...
if (is_file(DIR_custom_config.'config.inc.php') and is_readable(DIR_custom_config.'config.inc.php')) { require_once(DIR_custom_config.'config.inc.php'); }
// ... generales después
if (!is_file(DIR_config.'config.inc.php') or !is_readable(DIR_config.'config.inc.php')) { die("No puedo acceder al archivo de configuración general config.inc.php"); }
require_once(DIR_config.'config.inc.php');

// Cargar la biblioteca de funciones comunes.
if (!is_file(DIR_includes.'common.inc.php') or !is_readable(DIR_includes.'common.inc.php')) { die("No puedo acceder a la biblioteca de funciones comunes common.inc.php"); }
require_once(DIR_includes . "common.inc.php"); // Contiene las funciones comunes.

if (!ExisteArchivo(DIR_model.'class.dbutil.3.0.inc.php')) { die("No puedo acceder a la clase de acceso a base de datos class.dbutil.3.0.inc.php"); }
require_once(DIR_model . 'class.dbutil.3.0.inc.php'); // Clase base para el manejo de la base de datos.

/* Ahora se puede comenzar a usar el framework. */
$required_classes = [DIR_includes . "class.security.inc.php", DIR_model."class.contenidos.inc.php"];
if (ExisteArchivo('required_classes.php')) {
	include('required_classes.php');
}
if (CanUseArray($required_classes)) { foreach($required_classes as $class) { if (ExisteArchivo($class)) { include_once($class);	} else { echo (DEVELOPE)?'Clase requerida no encontrada): '.$class.FDL:null;	}}	unset($class); unset($required_classes); }

$asset = null; // Bandera para determinar si se carga contenido o recurso.
$msgerr = null; // Para almacenar cualquier mensaje de error.
$user_logged_in = false; // Determinar si el usuario está loggeado.
$for_files = false; // Determina si se carga un archivo asset directamente.
$asset = false; // Determina si el contenido a cargar es un asset.
$controllerpath = null; // Guarda la ruta hacia el controlador del contenido.

/*
$_GET['_ruta_'] se establece mediante el archivo .htaccess, contiene la lista de directorios virtuales.
*/
$handler = array(DEFAULT_CONTENT); // Acá van a quedar los alias de los contenidos encadenados como directorios virtuales. Por omisión, el contenido... por omisión.
if (isset($_GET[BASE_VPATH]) and ($_GET[BASE_VPATH] != null)) { // Si existe y no está vacío...
	$handler = cSecurity::ParsePath($_GET[BASE_VPATH]); // Convertir los dir virturales en un array.
}
// ShowDie($handler);
// $constants = get_defined_constants(true)['user'];
// ShowDie($constants);
//exit;

// Una instancia de la base de datos.
$objeto_db = new cDb(DBHOST, DBNAME, DBUSER, DBPASS, DBPORT);
$objeto_db->Connect();
// Primer problema: La conexión a la DB podría ser errónea por alguna razón...
if ($objeto_db->error) {
	include(DIR_errordocs."dberror.htm");
	exit;
}

$objeto_contenido = new cContenido(); // Instanciar la clase de acceso a los contenidos.

try {

	if (INTERFACE_TYPE == 'backend') {
		// Una instancia de la clase para manejar los usuarios del sistema.
		$objeto_usuario = new cUsrBackend();
		$user_logged_in = $objeto_usuario->CheckLogin();
		$objeto_contenido->usuario = @$objeto_usuario;
	}

	if (isset(ASSETS_CONTENTS[$handler[0]])) {
		$asset = true;
		$handler[0] = substr(strtolower($handler[0]),0,30);
		$controllerpath = DIR_controller.'controller_'.ASSETS_CONTENTS[$handler[0]]['controller'].'.php';
		array_shift($handler);
		if (strtolower(@$handler[0]) == strtolower(DEFAULT_FOR_FILES)) {
			$for_files = true;
			array_shift($handler);
		}
	}

	if (!$for_files and !$asset and (count($handler) > 0)) { // Si no se pide un archivo directamente...
		$content_found = $objeto_contenido->GetContent($handler); // Se busca el contenido.
		if ($content_found and (INTERFACE_TYPE == 'backend') and ($user_logged_in)){
			// $objeto_contenido->SetPermisos($objeto_usuario->tienePermiso($objeto_contenido->id));
		}
	}
	// Vamos a determinar quién es el controlador del contenido actual.
	if (!$asset) {
		if ((INTERFACE_TYPE == 'backend') and ($objeto_contenido->esta_protegido) and (!$user_logged_in) and (!$for_files)) {
			$objeto_contenido->GetContent('login');
		}
		if (!empty($objeto_contenido->controlador)) {
			$controllerpath = DIR_controller.'controller_'.$objeto_contenido->controlador.'.php';
		} else { // Last resource
			$controllerpath = DIR_controller.'controller_pagina.php';
		}
	}
	// ¿El archivo controlador existe y es accesible?.
	if (!ExisteArchivo($controllerpath)) {
		cLogging::Write(__FILE__." ".__LINE__." Controlador no encontrado: ".$controllerpath);
		$msgerr = 'Te olvidaste del controlador... '.$controllerpath;
		require_once(DIR_errordocs."500c.htm");
		throw new Exception($msgerr);
	}

	require_once($controllerpath); // Aquí se transfiere el control al controlador del contenido solicitado.

} catch(Exception $e) {
	cLogging::Write($e->GetFile()." ".$e->GetLine()." Error cacheado: ".$e->GetMessage());
} finally {
	$objeto_db->Disconnect(); // Siempre es buena idea no dejar abierta una conexión a la base de datos.
}
