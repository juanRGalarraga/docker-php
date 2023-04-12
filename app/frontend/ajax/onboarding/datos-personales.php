<?php
/**
	Esto viene de Datos Personales.
	
	Acá es donde se envía el pin al visitante porque el siguiente paso es la validación de PIN.
	
	Created: 2021-10-14
	Author: DriverOp
*/

$solicitud->SetSolicitud($onboarding->solic_id, ['plan'=>$cotizacion->Planid,'plazo'=>$cotizacion->Periodo,'capital'=>$cotizacion->Capital]);

require_once(DIR_model."smspin".DS."class.smspin.inc.php");

$smspin = new cSmsPin;

if ($pinId = $smspin->SendPinRequest($onboarding->solic_id)) {
	$onboarding->SetData(['pinId'=>$pinId,"retryPin"=>1,'retryTimeout'=>$smspin->retryTimeout??10]);
	return;
}

$stepMessage = "Servicio de validación de número de teléfono no está disponible<br />Por favor reintenta más tarde.";
include(onBoardingViews.$onboarding->GoStep('general-error')->vista);ResponseOk();
$continue = false;
