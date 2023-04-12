<?php
/*
	Controlador de los pasos del onboarding.
	Aquí llegan las peticiones desde el front via JS para verificar que el paso actual es correcto y enviar de regreso el siguiente paso.
	
	Created: 2021-09-03
	Author: DriverOp
	
	Notas:
		Desde el cliente me tiene que llegar el token y el campo data.
		Token contiene la referencia del paso actual que hay que evaluar, cifrado como un JWT.
		Data es un doc. JSON con los datos recolectados por el paso actual.
			A partir de él, se crea el objeto $data que contiene esos datos mapeados en sus propiedades.
*/


require_once(DIR_model."class.jwtbase.inc.php");
require_once(DIR_model."onboarding".DS."class.onboarding.inc.php");
require_once(DIR_model."calculadora".DS."class.calculadora.inc.php");
require_once(DIR_model."solicitudes".DS."class.solicitud.inc.php");
require_once(DIR_model."solicitudes".DS."class.solicitudLog.inc.php");
require_once(DIR_includes."class.checkinputs.inc.php");

$msgerr = array();
$prev = false;
$fail = false;
$post = CleanArray($_POST);
$continue = true;
$aca = EnsureTrailingSlash(dirname(__FILE__));
$msgerr = array();

$token = $post['token']??null;
$data = $post['data']??null;

if (empty($token)) {
	return EmitJSON('No token, no progress.');
}

if (!empty($data)) {
	if (IsJsonEx($data)) {
		$data = json_decode($data);
		if (json_last_error() !== JSON_ERROR_NONE) {
			cLogging::Write(__FILE__ ." ".__LINE__ ." El cliente envió un JSON no válido: ".(JSON_ERROR_MSG_ESP[json_last_error()]??null));
		}
		if (is_object($data)){
			if (isset($data->prev) and ($data->prev == true)) {
				$prev = true;
			}
			if (isset($data->fail) and ($data->fail == true)) {
				$fail = true;
			}
		}
	}
}


$calculadora = new cCalculadora();
$onboarding = new cOnBoarding();
if (!$onboarding->ValidToken($token)) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." Token no es válido o ha expirado.");
	$onboarding->Clear();
	ResponseOk(['restart'=>true]);
	$step = $onboarding->FirstStep();
	include(onBoardingViews.$step->vista);
	return;
}

if (!$onboarding->GoStep($onboarding->alias)) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." Oops! el paso indicado no existe: ".$onboarding->alias);
	$onboarding->Clear();
	ResponseOk(['restart'=>true]);
	$step = $onboarding->FirstStep();
	include(onBoardingViews.$step->vista);
	return;
}

$cotizacion = $calculadora->Get();
$solicitud = new cSolicitud();
$solicitudLog = new cSolicitudLog();
$solicitud->calculadora = $calculadora;

if ($prev) { // Si retrocede, no validar el paso actual.
	$step = $onboarding->PrevStep();
	include(onBoardingViews.$step->vista);
	ResponseOk();
	return;
}

if (empty($onboarding->solic_id)) { // Esto significa que todavía no se creó la solicitud en el core.
	if ($onboarding->crearSolicitud) // Si el paso actual admite crear solicitud...
		$solicitudLog->descripcion = "Nueva solicitud";
		$solicitudLog->tag = "INIT";
		$onboarding->setSolic_id($solicitud->InitSolicitud((array)$data+['plazo'=>$cotizacion->Periodo,'capital'=>$cotizacion->Capital]));
}

// Esto es para que en el core se lleve la cuenta de a qué paso llegó el visitante.
$data->paso_alias = $onboarding->alias;
$data->paso_descripcion = $onboarding->descripcion??null; 

$solicitudLog->solicitudId = $onboarding->solic_id;
$solicitudLog->Set($data);

$validador = $aca.$onboarding->alias.".php";
if (ExisteArchivo($validador)) {
	require_once($validador); // <-- Aquí está la validación de los datos enviados por el cliente del onBoarding. Es responsabilidad de este script enviarle los datos al core.
} else {
	// Si no hay validador, de todas formas guardamos los datos enviados por el cliente.
	$onboarding->SetData($data);
}

// Continuar el proceso?
if (!$continue) { return; }

if (!$solicitud->sent and $onboarding->crearSolicitud) { // Si la solicitud no ha sido enviada aún...
	$solicitud->SetSolicitud($onboarding->solic_id, (array)$data+['plazo'=>$cotizacion->Periodo,'capital'=>$cotizacion->Capital]);
}

if ($solicitud->http_nroerr == 403) {
	include(onBoardingViews.$onboarding->GoStep('forbidden')->vista);ResponseOk();return;
}
if (in_array($solicitud->http_nroerr, [406,500])) {
	$stepMessage = "Hemos detectado un error al procesar la solicitud.<br/>Por favor reintenta!";
	include(onBoardingViews.$onboarding->GoStep('general-error')->vista);ResponseOk();return;
}

// Cargar el siguiente paso.
$vista = onBoardingViews.$onboarding->NextStep()->vista;
if (ExisteArchivo($vista)) {
	include($vista);
	ResponseOk();
} else {
	if (DEVELOPE) { EchoLogP("Ups!, no encuentro el archivo ".$vista); }
	if (DEPLOY != 'prod') { EchoLog("<!-- Ups!, no encuentro el archivo ".$vista." -->"); }
}
