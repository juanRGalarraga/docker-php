<?php
/*
	Determinar si el PIN es correcto a partir del ID de solicitud.
	
	El cliente tiene que pasar los dos datos,
	El ID de la solicitud, ahí está almacenado el PINID para enviar a Infobip
	El PIN es lo que el solicitante en el front escribió.
	
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
	return $ws->SendResponse(406, 'La solicitud indicada no tiene id de Pin.', 69);
}

$pinid = $solicitud->data->pinId;
$pin = $ws->GetParam('pin'); // Esto es lo que escribió el solicitante.
if (empty($pin)) {
	return $ws->SendResponse(406, 'No se indicó PIN.', 67);
}

require($aca."InfobipCheck.php");

if (!$continue) { return; }

$ws->SendResponse(200, $infobip_response["response"]);
