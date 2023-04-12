<?php
/**
* Verificar el código de aceptación
* Created: 2021-10-20
* Author: DriverOp
*/


require_once(DIR_model. "solicitudes". DS ."class.modifSolicitud.inc.php");

$solicitudid = $ws->params['id'];
$codigo_aceptacion = $ws->GetParam(['codigo_aceptacion','cod','codacc']);

if (empty($solicitudid)) {
	return $ws->SendResponse(400, 'No se indicó ID de solicitud', 14);
}
if (empty($codigo_aceptacion)) {
	return $ws->SendResponse(400, 'No se indicó código de aceptación', 80);
}

$solicitud = new cModifSolicitud;

if (!$solicitud->Get($solicitudid)) {
	return $ws->SendResponse(404, 'Solicitud no encontrada.');
}

if ($solicitud->data->emailVerified??false) {
	return $ws->SendResponse(406,['codigo_aceptacion'=>'Email ya fue verificado.'],71);
}

if (empty($solicitud->data->codigoAceptacion)) {
	return $ws->SendResponse(406,['codigo_aceptacion'=>'La solicitud no tiene código de aceptación.'],81);
}

$codigo_aceptacion = strtolower(substr($codigo_aceptacion,0,5));

if ($codigo_aceptacion != $solicitud->data->codigoAceptacion) {
	return $ws->SendResponse(406,['codigo_aceptacion'=>'Código de aceptación no corresponde a la solicitud'],82);
}

$ws->SendResponse(200, 'Solicitud aceptada');


$solicitud->data->emailVerified = true;
$solicitud->data = $solicitud->data;
$solicitud->Set();