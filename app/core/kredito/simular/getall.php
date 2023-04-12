<?php
/*
	Devuelve una cotización completa para ser usada en el backend.
	Es necesario que se indique el ID del usuario en el backend usando la cabecera Backend-Id en la petición.
	
	Created: 20211-11-17
	Author: DriverOp

*/



if (empty($ws->backendUserId)) {
	return $ws->SendResponse(400, 'No se indicó ID de usuario backend.');
}
require_once(DIR_model."backenduser".DS."class.backenduser.inc.php");
$bkuser = new cBackendUser;

$userdata = $bkuser->GetAll($ws->backendUserId);

if (!$userdata) {
	return $ws->SendResponse(415, 'Usuario backend indicado no existe.');
}

require_once(DIR_model."planes".DS."class.planes.inc.php");

$monto = $ws->GetParam(['monto','capital','total']);
$plazo = $ws->GetParam(['plazo']);
$plan_id = $ws->GetParam(['plan','planid','plan_id']);


$msgerr = array();

if (!empty($plan_id)) {
	$plan_id = SecureInt($plan_id,0);
}

$planes = new cPlanes();

if ($plan_id != 0) {
	if (!$plan = $planes->Get($plan_id)) {
		return $ws->SendResponse(404, 'Plan no encontrado. El ID de plan debe ser explícito.');
	}
} else {
	if (!$plan = $planes->GetDefault($userdata->negocio_id)) {
		return $ws->SendResponse(404, 'Plan no encontrado.');
	}
}

/* TODO: Controlar que el plan pertenezca al mismo negocio que el usuario del Backend. */

if ($plan->estado != 'HAB') {
	return $ws->SendResponse(404, 'Plan no está habilidado.');
}

if (!empty($plan->vigencia_desde) and $plan->vigencia_desde > cFechas::Hoy()) {
	return $ws->SendResponse(404, 'Plan no está en vigencia.');
}
if (!empty($plan->vigencia_hasta) and $plan->vigencia_hasta < cFechas::Hoy()) {
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

require_once(DIR_model."simulador".DS."class.simulador.inc.php");

$simulador = new cSimulador($planes);
$simulador->cantDecs = 0;


$salida = $simulador->Calcular(['monto'=>$monto, 'plazo'=>$plazo, 'plan'=>$plan]);
if ($salida === false) {
	return $ws->SendResponse(500, $simulador->msgerr);
}

if ($plan->tipo_pagos == 'unico') {
	return $ws->SendResponse(200, $simulador->totales);
}
$salida = array(
	'totales'=>$simulador->calculo->totales,
	'tasas'=>$simulador->calculo->tasas,
	'cuotas'=>$simulador->calculo->cuotas
);

$ws->SendResponse(200, $salida);

