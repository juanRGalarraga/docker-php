<?php
/*
	Validar el PIN escrito por el solicitante.
	Created: 2021-10-18
	Author: DriverOp
*/


if (!is_object($data)) { $continue = EmitJSON('No se recibieron datos'); return; }

$data->PIN = substr($data->PIN??null,0,16);

require_once(DIR_model."smspin".DS."class.smspin.inc.php");

$smspin = new cSmsPin;

if ($smspin->CheckPinRequest($data->PIN,$onboarding->solic_id)) {
	$onboarding->SetData(['pinVerified'=>true]);
	return;
}

if ($smspin->http_errnro == 500) {
	$stepMessage = "Servicio de validación de número de teléfono no está disponible<br />Por favor reintenta más tarde.";
	include(onBoardingViews.$onboarding->GoStep('general-error')->vista);ResponseOk();
	$continue = false;
	return;
}

$continue = EmitJSON(['PIN'=>'Pin no es correcto']);