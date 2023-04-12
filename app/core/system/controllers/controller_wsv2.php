<?php
/*
	Controlador principal de la API REST
	Created: 2021-05-04
	Author: DriverOp
*/
defined("WS_DEBUG_LEVEL") || define("WS_DEBUG_LEVEL",2);
defined("DIR_BASE_CUSTOM") || define("DIR_BASE_CUSTOM",DIR_BASE.DEVELOPE_NAME.DS); // Directorio donde están los .json con las definiciones de las apis.

require_once(DIR_model."class.showWarning.inc.php");
require_once(DIR_model."wsv2".DS."class.wsv2Core.inc.php");
require_once(DIR_model."wsv2".DS."class.wsv2Users.inc.php");
require_once(DIR_model."wsv2".DS."class.wsv2Apis.inc.php");

$showWarning = new cShowWarning();
$ws_usuario = new cWsUsuario();
$ws = new cWebService($ws_usuario);
$ws->baseDir = DIR_BASE.DEVELOPE_NAME.DS; // Este es el directorio donde están los resolvers.
$ws->DebugOutput = true;

$reglog = array();

if (WS_DEBUG_LEVEL > 0) {
	$reglog['Referer'] = (isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'(not set)';
	$reglog['Method'] = (!empty($_SERVER['REQUEST_METHOD']))?$_SERVER['REQUEST_METHOD']:'(not set)';
	$reglog['URI'] = (!empty($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:'(not set)';
	if (WS_DEBUG_LEVEL > 1) {
		$reglog['REQUEST'] = print_r($_REQUEST,true);
		$reglog['Headers'] = print_r(getallheaders(),true);
		$reglog['Body'] = file_get_contents('php://input');
	}
	$linea = PHP_EOL;
	foreach($reglog as $key => $value) {
		$linea .= $key.': '.$value.PHP_EOL;
	}
	cLogging::Write($linea);
}

// ShowVar($ws->headers);
// ShowVar($ws->body);
// ShowVar($ws);
// ShowVar($ws_usuario);


$apis = new cApis();

$apis->GetRoutes($ws->method);
// Eliminar el 'v2' de la URI.
$apiUri = preg_replace('~^v2/~i',null,$_GET[BASE_VPATH]); 

$datosContenido = $apis->ParseURL($apiUri);

if (empty($datosContenido)) {
	$ws->SendResponse(404,'Servicio no encontrado o parámetros no son aceptables',42); return;
}

$ws->GetResolver($datosContenido);
if ($ws->error) { 
	$ws->SendResponse(500, (!DEVELOPE)?"Error interno del servidor.":$ws->msgerr, 106);
	return;
}

cLogging::Write('Resolver: '.print_r($ws->theResolver,true));

if (empty($ws->theResolver)) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." datosContenido: ".print_r($datosContenido,true));
	$ws->SendResponse(500,'Resolver no encontrado (theResolver está vacío).',102); return;
}
if ($ws->restricted) {
	if (!$ws->GetWsUser()) { return; }
}
if (is_string($ws->theResolver)) {
	include($ws->theResolver);
} else {
	if (is_array($ws->theResolver)) {
		foreach($ws->theResolver as $theFile) {
			include($theFile);
			if (!$ws->continue) { break; }
		}
	}
}

/* Esto es en caso que el resolver no devuelva una respuesta, el cliente que hizo la petición no se quede sin una. */
if ($ws->continue) {
	$ws->SendResponse(200,'Servicio atendido');
}
cLogging::Write(__FILE__ ." ".__LINE__);
