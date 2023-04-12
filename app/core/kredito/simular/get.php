<?php
/**
* Devuelve una cotización para el simulador/calculadora del frontend. Solamente devuelve la cotización para el plan por omisión del negocio del cliente del API.
* Created: 2021-08-30 15:20:14
* Author: DriverOp
*/

require_once(DIR_model."planes".DS."class.planes.inc.php");

$monto = $ws->GetParam(['monto','capital','total']);
$plazo = $ws->GetParam(['plazo']);
$plan_id = $ws->GetParam(['plan','planid','plan_id']);
$solicitud_id = $ws->GetParam(['solicid','solicitudid','solic_id','solicitud_id',]);

$msgerr = array();

if (!empty($plan_id)) {
	$plan_id = SecureInt($plan_id,0);
}

$planes = new cPlanes();

if ($plan_id != 0) {
	if (!$plan = $planes->Get($plan_id)) {
		return $ws->SendResponse(404, 'Plan no encontrado.');
	}
} else {
	if (!$plan = $planes->GetDefault($ws_usuario->negocio_id)) {
		return $ws->SendResponse(404, 'Plan no encontrado.');
	}
}

if ($plan->estado != 'HAB') {
	return $ws->SendResponse(404, 'Plan no está habilidado.');
}

if ($plan->vigencia_desde > cFechas::Hoy()) {
	return $ws->SendResponse(404, 'Plan no está en vigencia.');
}
if ($plan->vigencia_hasta < cFechas::Hoy()) {
	return $ws->SendResponse(404, 'Plan ya no está en vigencia.');
}
if (empty($monto)) {
	$monto = $plan->monto_minimo+(($plan->monto_maximo-$plan->monto_minimo)/2);
} else {
	if (!CheckFloat($monto)) {
		$msgerr['monto'] = 'Monto no es un número.';
	} else {
		$monto = ((float)$monto)*1;
	}
}
if (empty($plazo)) {
	$plazo = floor($plan->plazo_minimo+(($plan->plazo_maximo-$plan->plazo_minimo)/2));
} else {
	if (!CheckInt($plazo)) {
		$msgerr['plazo'] = 'Plazo no es un número.';
	} else {
		$plazo = ((int)$plazo)*1;
	}
}
if ($monto > $plan->monto_maximo) {
	$msgerr['monto'] = 'Monto excede el máximo.';
}

if ($monto < $plan->monto_minimo) {
	$msgerr['monto'] = 'Monto por debajo del mínimo.';
}

if ($plazo > $plan->plazo_maximo) {
	$msgerr['plazo'] = 'Plazo excede el máximo.';
}

if ($plazo < $plan->plazo_minimo) {
	$msgerr['plazo'] = 'Plazo por debajo del mínimo.';
}

if (CanUseArray($msgerr)) {
	return $ws->SendResponse(412, $msgerr,11);
}

/* NOTA: Debería también controlar que el plan pertenezca al mismo negocio que el usuario del WS. */

require_once(DIR_model."simulador".DS."class.simulador.inc.php");

$simulador = new cSimulador($planes);
$simulador->cantDecs = 0;


$salida = $simulador->Calcular(['monto'=>$monto, 'plazo'=>$plazo, 'plan'=>$plan]);
if ($salida === false) {
	return $ws->SendResponse(500, $simulador->msgerr);
}

$salida = $simulador->GenerarSalida('cotiz');
if (!$salida) {
	return $ws->SendResponse(500, $simulador->msgerr);
}

$ws->SendResponse(200, $salida);

if (empty($solicitud_id)) { return; }
// Solo si el cliente envió el ID de solicitud 

require_once(DIR_model."solicitudes". DS ."class.solicitudesLogs.inc.php");
require_once(DIR_model. "solicitudes". DS ."class.modifSolicitud.inc.php");

$solicitud = new cModifSolicitud;

if (!$solicitud->Get($solicitud_id)) {
	cLogging::Write(__FILE__ ." ".__LINE__ ." La solicitud no fue encontrada: $solicitud_id");
	return;
}
$solicitud->cotizacion = $salida;
$solicitud->Set();
