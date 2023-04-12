<?php
/**
* Enviar el correo de pedido de aceptación al mail registrado en la solicitud
* Created: 2021-10-19
* Author: DriverOp
*/


require_once(DIR_includes."libfnckeys.php");
require_once(DIR_model. "solicitudes". DS ."class.modifSolicitud.inc.php");
require_once(DIR_model."emails".DS."class.sendacceptance.inc.php");

$solicitudid = $ws->params['id'];

if (empty($solicitudid)) {
	return $ws->SendResponse(400, 'No se indicó ID de solicitud', 14);
}


$solicitud = new cModifSolicitud;

if (!$solicitud->Get($solicitudid)) {
	return $ws->SendResponse(404, 'Solicitud no encontrada.');
}

if (empty($solicitud->data->email)) {
	return $ws->SendResponse(406, 'Solicitud no tiene email',70);
}

if ($solicitud->data->emailVerified??false) {
	return $ws->SendResponse(406,['email'=>'Email ya fue verificado.'],71);
}

$codigo_aceptacion = UnambiguousRandomChars(5,true);

$tags = array();
$tags['nombre'] = $solicitud->data->nombre??null;
$tags['apellido'] = $solicitud->data->apellido??null;
$tags['capital'] = F($solicitud->cotizacion->Capital??null);
$tags['total'] = F($solicitud->cotizacion->Total??null);
$tags['plazo'] = F($solicitud->cotizacion->Periodo??null);
$tags['fecha_vencimiento'] = cFechas::SQLDate2Str($solicitud->cotizacion->Fecha_Pago??null, CDATE_IGNORETIME+CDATE_NOWEEKDAY);
$tags['codigo_aceptacion'] = $codigo_aceptacion;


$sendmail = new cSendAcceptance($sysParams);

$sendmail->Subject = "Aceptación de préstamo (".cFechas::Ahora().")";

// Se envía el correo...
if (!$sendmail->Send($tags)) {
	return $ws->SendResponse(500, 'No se pudo enviar correo de aceptación');
}

// ...y se guarda el código en la solicitud.
$solicitud->data->codigoAceptacion = $codigo_aceptacion;
$solicitud->data = $solicitud->data;
$solicitud->Set();
