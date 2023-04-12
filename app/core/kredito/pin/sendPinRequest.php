<?php
/*
	Pedirle a infobip que envíe un pin al número de teléfono indicado en la solicitud.
*/

$aca = __DIR__.DS;
$continue = true;

require_once(DIR_includes."class.checkinputs.inc.php");
require_once(DIR_model. "solicitudes". DS ."class.modifSolicitud.inc.php");

$solicitudid = $ws->GetParam('solicitudid');

if (empty($solicitudid)) {
	return $ws->SendResponse(400, 'NO se indicó id de solicitud');
}

$solicitud = new cModifSolicitud;

if (!$solicitud->Get($solicitudid)) {
	return $ws->SendResponse(404, 'Solicitud no encontrada.');
}

if (empty($solicitud->data->tel)) {
	return $ws->SendResponse(406, 'La solicitud indicada no tiene número de teléfono.', 65);
}

if (!cCheckInput::Tel($solicitud->data->tel)) {
	return $ws->SendResponse(406,['tel'=>'Número de teléfono no es válido.'],68);
}
if ($solicitud->data->telVerified??false) {
	return $ws->SendResponse(406,['tel'=>'Número de teléfono ya fue verificado.'],66);
}

// Ahora sí, vamos con el PIN.

$telnumber = $solicitud->data->tel;
require($aca."InfobipSend.php");

$solicitud->AddDataAndSet(['pinId'=>$infobip_response["response"]["pinId"],'retryPin'=>1]);

if (!$continue) { return; }

$ws->SendResponse(200, $infobip_response["response"]);


