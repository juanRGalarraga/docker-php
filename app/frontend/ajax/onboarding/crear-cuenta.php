<?php
/*
	Validación del paso 'crear-cuenta'.
	Created: 2021-09-06
	Author: DriverOp
*/

require_once(DIR_model."personas".DS."class.personas.inc.php");
require_once(DIR_includes."class.checkinputs.inc.php");

$data->nro_doc = substr(trim($data->nro_doc),0,10);
$data->password = substr(trim($data->password),0,32);
$data->checkTyc = $data->checkTyc??false;
$data->checkPdP = $data->checkPdP??false;
$data->checkAuth = $data->checkAuth??false;

// Respaldar lo que el visitante escribió, aunque sea erróneo.
$onboarding->SetData($data);

cCheckInput::DNI($data->nro_doc,'nro_doc','número de documento');
cCheckInput::StrongPassword($data->password,'password','una contraseña');
if (!$data->checkTyc) {
	$msgerr['checkTyc'] = 'Debes aceptar términos y condiciones';
}
if (!$data->checkPdP) {
	$msgerr['checkPdP'] = 'Debes aceptar políticas de privacidad';
}
if (!$data->checkAuth) {
	$msgerr['checkAuth'] = 'Debes autorizarnos';
}

$msgerr = array_merge($msgerr, cCheckInput::$msgerr);
if (CanUseArray($msgerr)) {
	EmitJSON($msgerr);
	$continue = false;
	return;
}

// $data contiene los datos de este paso.
$solicitud->SetSolicitud($onboarding->solic_id, (array)$data+['plan'=>$cotizacion->Planid,'plazo'=>$cotizacion->Periodo,'capital'=>$cotizacion->Capital]);

$persona = new cPersona();

$persona->GetByNroDoc($data->nro_doc);
if ($persona->http_nroerr == 200) {
	$stepMessage = 'Vemos que ya tenés una cuenta con nosotros.<br/>Debés ingresar a Mi Cuenta para obtener un nuevo préstamo.';
	include(onBoardingViews.$onboarding->GoStep('forzar-micuenta')->vista);	ResponseOk(); $continue = false;
	return;
}

if ($solicitud->http_nroerr == 400) {
	// Si el core devolvió status 400 quiere decir que no le gustaron los datos enviados. Vamos a ver cuáles son
	EmitJSON($solicitud->dataerr??'Servidor rechazó la solicitud.');
	$continue = false;
	return;
}