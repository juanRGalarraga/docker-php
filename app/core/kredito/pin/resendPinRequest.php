<?php
/*
	Pedirle a infobip que reenvíe el pin al PinID indicado en la solicitud.
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

if (empty($solicitud->data->pinId)) {
	return $ws->SendResponse(406, 'La solicitud indicada no tiene PinID.', 65);
}

if ($solicitud->data->telVerified??false) {
	return $ws->SendResponse(406,['tel'=>'Número de teléfono no es válido.'],66);
}

// Ahora sí, vamos con el PIN.

$telnumber = $solicitud->data->tel;
$pinId = $solicitud->data->pinId;
$retryPin = $solicitud->data->retryPin??1;
require($aca."InfobipReSend.php");

if (!$continue) { return; }
$solicitud->AddDataAndSet(['retryPin'=>$retryPin++]); // Adelatar el contador de reintentos solo si se pudo enviar.


$ws->SendResponse(200, $infobip_response["response"]);


