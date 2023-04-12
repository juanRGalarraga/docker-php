<?php

if (!is_object($data)) { $continue = EmitJSON('No se recibieron datos'); return; }

$data->telcod = substr($data->telcod??null,0,4);
$data->telnum = substr($data->telnum??null,0,8);
$data->email = mb_substr($data->email??null,0,100);
$data->optin = $data->optin??null;

// Respaldar lo que el visitante escribió, aunque sea erróneo.
$onboarding->SetData($data);

if (!empty($data->telcod)) {
	if (!is_numeric($data->telcod)) {
		$msgerr['telcod'] = 'Código de área debe ser un número';
	}
} else {
	$msgerr['telcod'] = 'Debes completar código de área';
}

if (!empty($data->telnum)) {
	if (!is_numeric($data->telnum)) {
		$msgerr['telnum'] = 'Número de abonado debe ser un número';
	}
} else {
	$msgerr['telnum'] = 'Debes completar número de abonado';
}

if (!cCheckInput::IsEmail($data->email)) {
	$msgerr['email'] = 'Dirección de correo electrónico no válida.';
}

if (CanUseArray($msgerr)) {
	$continue = EmitJSON($msgerr);
	return;
}

if (strlen($data->telcod) < 2) {
	$msgerr['telcod'] = 'Código de área debe ser al menos dos cifras';
}

if (strlen($data->telnum) < 6) {
	$msgerr['telcod'] = 'Número de abonado debe ser al menos seis cifras';
}

if (CanUseArray($msgerr)) {
	$continue = EmitJSON($msgerr);
	return;
}

switch (strlen($data->telcod)) {
	case 2: if (strlen($data->telnum) != 8) { $msgerr['telnum'] = 'Deben ser ocho números'; } break;
	case 3: if (strlen($data->telnum) != 7) { $msgerr['telnum'] = 'Deben ser siete números'; } break;
	case 4: if (strlen($data->telnum) != 6) { $msgerr['telnum'] = 'Deben ser seis números'; } break;
}

if (CanUseArray($msgerr)) {
	$continue = EmitJSON($msgerr);
	return;
}

$tel = $data->telcod.'-'.$data->telnum;
if (isset($onboarding->solictemp->data->tel) and ($onboarding->solictemp->data->tel != $tel)) {
	$data->retryPin = 0; // Esto es para resetear los reintentos si el visitente cambió el número.
}
$data->tel = $tel;

/* 
	Teniendo el DNI, vamos al buró para traer el nombre y el apellido y el score.
	------------------------ Vamos al buró ------------------------
*/
require_once(DIR_model."buro".DS."class.buro.inc.php");

$buro = new cBuro();

$score = $buro->GetScore($onboarding->solictemp->data->nro_doc);
// Seleccionamos lo que nos interesa.
$data->score = $score->Score ?? null;
$data->nombre = $score->Nombre ?? null;
$data->apellido = $score->Apellido ?? null;
$data->direccion = $score->Direccion ?? null;

$onboarding->SetData($data);
// $data contiene los datos de este paso.
$solicitud->SetSolicitud($onboarding->solic_id, (array)$data+['plan'=>$cotizacion->Planid,'plazo'=>$cotizacion->Periodo,'capital'=>$cotizacion->Capital]);


if ($buro->http_nroerr >= 400) {
	$stepMessage = "No pudimos validar tus datos en este momento.<br />Por favor intenta de nuevo más tarde.";
	include(onBoardingViews.$onboarding->GoStep('general-error')->vista);ResponseOk();
	$continue = false;
	return;
}
