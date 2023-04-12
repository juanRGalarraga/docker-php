<?php
/**
	Verificar el código de aceptación y si es correcto, APROBAR EL PRÉSTAMO.
	Created: 2021-10-20
*/

$data->codigo_aceptacion = substr($data->codigo_aceptacion,0,15);

$onboarding->SetData($data);

if (empty($data->codigo_aceptacion)) {
	$continue = EmitJSON(['aceptacion'=>'Debes escribir el código']);
	return;
}


require_once(DIR_model."email".DS."class.sendmails.inc.php");

$sendmail = new cSendMail;

if (!$sendmail->CheckAceptance($onboarding->solic_id, strtolower($data->codigo_aceptacion))) {
	if ($sendmail->http_nroerr == 406) {
		EmitJSON(['codigo_aceptacion'=>($sendmail->theData->codigo_aceptacion??'')]);
	} else {
		EmitJSON('No se pudo verificar código de aceptación');
	}
	$continue = false;
	return;
}

/**
	Pedir al core que apruebe la solicitud
*/

if (!$solicitud->Aprobar($onboarding->solic_id)) {
	$stepMessage = "Ocurrió un problema al tratar de aprobar la solicitud.";
	$stepMessage .= (DEVELOPE)?'<br />'.$solicitud->msgerr:'';
	include(onBoardingViews.$onboarding->GoStep('general-error')->vista);ResponseOk();
	$continue = false;
	return;
}

$onboarding->SetData(['prestamo_id'=>$solicitud->theData, 'estado'=>'ENDOK']);