<?php
/*
	Validar los datos ingresados por el visitante cuando se le pide el CBU.
	Created: 2021-10-02
	Author: DriverOp
*/

require_once(DIR_includes."class.checkinputs.inc.php");
require_once(DIR_model."personas".DS."class.personas.inc.php");
require_once(DIR_model."params".DS."class.remoteParams.inc.php");
require_once(DIR_model."cuentabanco".DS."class.cbu.inc.php");


$data->cbu = substr(trim((string)$data->cbu),0,22);

$data->checkDeb = $data->checkDeb??false;

// Respaldar lo que el visitante escribió, aunque sea erróneo.
$onboarding->SetData($data);

cCheckInput::CBU($data->cbu,'cbu','CBU');
if (!$data->checkDeb) {
	$msgerr['checkDeb'] = 'Debes aceptar que se te debite el monto a devolver.';
}

$msgerr = array_merge($msgerr, cCheckInput::$msgerr);
if (CanUseArray($msgerr)) {
	EmitJSON($msgerr);
	$continue = false;
	return;
}

if ($remoteParam->Get('check_cbu_bind')) {
	$cbuCheck = new cCBU;
	$cbuCheck->GetInfo($data->cbu, $onboarding->solic_id);
	if ($cbuCheck->http_nroerr >= 400) {
		EmitJSON(['cbu' => $cbuCheck->msgerr]);
		$continue = false;
		return;
	}
}


// $data contiene los datos de este paso.
$solicitud->SetSolicitud($onboarding->solic_id, (array)$data+['plan'=>$cotizacion->Planid,'plazo'=>$cotizacion->Periodo,'capital'=>$cotizacion->Capital]);

$persona = new cPersona();

$persona->GetByNroCBU($data->cbu);
if ($persona->http_nroerr == 200) {
	EmitJSON('El CBU ingresado pertenece a otro titular.');
	$continue = false;
	return;
}

