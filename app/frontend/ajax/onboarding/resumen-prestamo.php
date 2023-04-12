<?php
/*
	Aquí se envía el email de aceptación hacia el cliente.
	Created: 2021-10-19
	Author: DriverOp
*/

if (empty($onboarding->solictemp->data->email)) {
	EchoLog('Vamos a telefono mail');
	include(onBoardingViews.$onboarding->GoStep('telefono-mail')->vista);ResponseOk();
	$continue = false;
	return;
}

require_once(DIR_model."email".DS."class.sendmails.inc.php");

$sendmail = new cSendMail;

if ($sendmail->SendAceptance($onboarding->solic_id)) {
	
}