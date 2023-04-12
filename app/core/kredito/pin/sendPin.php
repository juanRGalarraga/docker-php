<?php
/*
	Pedirle a infobip que envíe un pin a un teléfono
*/
$aca = __DIR__.DS;
$continue = true;
require_once(DIR_includes."class.checkinputs.inc.php");

$telnumber = $ws->GetParam(['telnumber','tel','telnum']);

if (empty($telnumber)) {
	$ws->SendResponse(400,['tel'=>'Se debe indicar número de teléfono'],10); return;
}

if (!cCheckInput::Tel($telnumber)) {
	$ws->SendResponse(400,['tel'=>'Número de teléfono no es válido.'],68); return;
}

// EL número de teléfono debería tener el código de país, pero si no lo tiene, agregar Argentina por omisión.
if (strlen($telnumber) <= 10) {
	$telnumber = '54'.$telnumber;
}

if ($sysParams->Get('infobip_modo_test', false)) {
	$ws->SendResponse(200, ["pinId"=>md5($telnumber), "to"=>$telnumber, "ncStatus"=>"NC_NOT_CONFIGURED", "smsStatus"=>"MESSAGE_SENT", "test"=>"on"]); return;
}

require($aca."InfobipSend.php");

if (!$continue) { return; }

$ws->SendResponse(200, $infobip_response["response"]);
