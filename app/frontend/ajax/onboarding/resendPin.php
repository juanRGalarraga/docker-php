<?php
require_once(DIR_model."onboarding".DS."class.onboarding.inc.php");
require_once(DIR_model."calculadora".DS."class.calculadora.inc.php");
require_once(DIR_model."smspin".DS."class.smspin.inc.php");


$post = CleanArray($_POST);
$token = $post['token']??null;

if (empty($token)) {
	return EmitJSON('No token, no progress.');
}


$smspin = new cSmsPin;
$onboarding = new cOnBoarding();
if (!$onboarding->ValidToken($token)) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." Token no es válido o ha expirado.");
	$onboarding->Clear();
	ResponseOk(['restart'=>true]);
	return;
}

if (($onboarding->solictemp->data->retryPin??1) >= $smspin->retryNumber) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." Demasiados reintentos.");
	
	$calculadora = new cCalculadora();
	$calculadora->Get();
	
	ResponseOk(['gostep'=>true,'msg'=>'Demasiados reintentos, ingresa un nuevo número de teléfono']);
	include(onBoardingViews.$onboarding->GoStep('telefono-mail')->vista);ResponseOk();
	return;
}

$retryPin = ($onboarding->solictemp->data->retryPin??1);

if ($smspin->ResendPinRequest($onboarding->solic_id)) {
	$retryPin++;
	$onboarding->SetData(['retryPin'=>$retryPin]);
	ResponseOk(['msg'=>'PIN reenviado','retryPin'=>($retryPin < $smspin->retryNumber)]);
	return;
}

ResponseOk(['msg'=>'No se pudo reenviar PIN','retryPin'=>($retryPin < $smspin->retryNumber)]);

